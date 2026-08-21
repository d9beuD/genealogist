<?php

declare(strict_types=1);

namespace App\Application\Gedcom;

use App\Dto\GedcomImportOutput;
use App\Dto\GedcomImportWarningOutput;
use App\Dto\TreeOutput;
use App\Entity\GedcomIdentifier;
use App\Entity\GedcomImport;
use App\Entity\GedcomMetadata;
use App\Entity\Person;
use App\Entity\Tree;
use App\Entity\Union;
use App\Entity\User;
use App\Gedcom\GedcomDocument;
use App\Gedcom\GedcomImportException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class ImportGedcom
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        #[Autowire('%kernel.project_dir%/public/pictures/gedcom')]
        private string $mediaDirectory,
    ) {}

    public function __invoke(GedcomDocument $document, User $owner, string $filename, ?Tree $targetTree, ?string $treeName): GedcomImportOutput
    {
        $this->validateReferences($document);
        $created = $this->emptyCounters();
        $updated = $this->emptyCounters();
        $writtenMedia = [];
        $this->entityManager->beginTransaction();
        try {
            $tree = $targetTree ?? new Tree()
                ->setUser($owner)
                ->setName($this->treeName($treeName, $filename))
                ->setCreatedAt(new \DateTimeImmutable());
            if (!$targetTree instanceof \App\Entity\Tree) {
                $this->entityManager->persist($tree);
            }

            $import = $this->entityManager->getRepository(GedcomImport::class)->findOneBy(['tree' => $tree, 'filename' => $filename]);
            $isReimport = $import instanceof GedcomImport;
            if (!$import instanceof GedcomImport) {
                $import = new GedcomImport($filename, $document->format)->setTree($tree);
                $this->entityManager->persist($import);
            } else {
                $import->setFormat($document->format);
                $this->entityManager->createQuery('DELETE FROM App\\Entity\\GedcomMetadata metadata WHERE metadata.import = :import')
                    ->setParameter('import', $import)->execute();
            }

            $this->entityManager->flush();

            $identifiers = $this->identifiersFor($import);
            $people = [];
            foreach ($document->records as $record) {
                if ('INDI' !== $record['tag']) {
                    continue;
                }

                $xref = $this->xref($record);
                $identifier = $identifiers['INDI:' . $xref] ?? null;
                $person = $identifier?->getPerson();
                if (!$person instanceof Person) {
                    $person = new Person();
                    $person->setTree($tree);
                    $identifier = new GedcomIdentifier($import, 'INDI', $xref)->setPerson($person);
                    $this->entityManager->persist($person);
                    $this->entityManager->persist($identifier);
                    ++$created['people'];
                } else {
                    ++$updated['people'];
                }

                $this->applyPerson($person, $record);
                $people[$xref] = $person;
                $identifiers['INDI:' . $xref] = $identifier;
            }

            foreach ($document->records as $record) {
                if ('FAM' !== $record['tag']) {
                    continue;
                }

                $xref = $this->xref($record);
                $identifier = $identifiers['FAM:' . $xref] ?? null;
                $union = $identifier?->getUnion();
                if (!$union instanceof Union) {
                    $union = new Union();
                    $identifier = new GedcomIdentifier($import, 'FAM', $xref)->setUnion($union);
                    $this->entityManager->persist($union);
                    $this->entityManager->persist($identifier);
                    ++$created['unions'];
                } else {
                    foreach ($union->getPeople()->toArray() as $person) {
                        $union->removePerson($person);
                    }

                    foreach ($union->getChildren()->toArray() as $child) {
                        $union->removeChild($child);
                    }

                    ++$updated['unions'];
                }

                $this->applyUnion($union, $record, $people);
                $identifiers['FAM:' . $xref] = $identifier;
            }

            if ($isReimport) {
                $this->persistMetadata($document, $import, $people, $identifiers, $updated);
            } else {
                $this->persistMetadata($document, $import, $people, $identifiers, $created);
            }

            $this->entityManager->flush();
            $this->publishMedia($document, $import, $writtenMedia);
            $created['media'] += \count($writtenMedia);
            $this->entityManager->commit();
        } catch (\Throwable $exception) {
            $this->entityManager->rollback();
            foreach ($writtenMedia as $path) {
                if (is_file($path)) {
                    unlink($path);
                }
            }

            if ($exception instanceof GedcomImportException) {
                throw $exception;
            }

            throw new GedcomImportException('GEDCOM import failed and was rolled back.', $exception->getCode(), previous: $exception);
        }

        return new GedcomImportOutput(
            new TreeOutput($tree->getId() ?? 0, $tree->getName() ?? '', $tree->getCreatedAt() ?? new \DateTimeImmutable('@0')),
            $document->format,
            $created,
            $updated,
            array_map(static fn(array $warning): GedcomImportWarningOutput => new GedcomImportWarningOutput($warning['code'], $warning['line'], $warning['tag'], $warning['message']), $document->warnings),
        );
    }

    /** @return array<string, GedcomIdentifier> */
    private function identifiersFor(GedcomImport $import): array
    {
        $identifiers = $this->entityManager->getRepository(GedcomIdentifier::class)->findBy(['import' => $import]);

        return array_reduce($identifiers, static function (array $result, GedcomIdentifier $identifier): array {
            $result[$identifier->getRecordType() . ':' . $identifier->getExternalId()] = $identifier;
            return $result;
        }, []);
    }

    /** @param array<string, Person> $people
     * @param non-empty-array<string, mixed> $record */
    private function applyUnion(Union $union, array $record, array $people): void
    {
        $marriage = $this->child($record, 'MARR');
        $union->setMarried(null !== $marriage);
        if (\is_array($marriage)) {
            $date = $this->value($this->child($marriage, 'DATE'));
            $union->setStartsAtGedcomDate($date)->setStartsAt($this->normalDate($date));
            $union->setPlace($this->value($this->child($marriage, 'PLAC')));
        }

        $divorce = $this->child($record, 'DIV');
        if (\is_array($divorce)) {
            $date = $this->value($this->child($divorce, 'DATE'));
            $union->setEndsAtGedcomDate($date)->setEndsAt($this->normalDate($date));
        }

        foreach (['HUSB', 'WIFE'] as $tag) {
            $xref = $this->value($this->child($record, $tag));
            if (isset($people[$xref])) {
                $union->addPerson($people[$xref]);
            }
        }

        foreach ($this->children($record, 'CHIL') as $child) {
            $xref = (string) $child['value'];
            if (isset($people[$xref])) {
                $union->addChild($people[$xref]);
            }
        }
    }

    /**
     * @param non-empty-array<string, mixed> $record
     */
    private function applyPerson(Person $person, array $record): void
    {
        $name = $this->value($this->child($record, 'NAME'));
        [$firstname, $lastname] = $this->splitName($name);
        $person->setFirstname($firstname)->setLastname($lastname);
        $person->setGender(match ($this->value($this->child($record, 'SEX'))) {
            'M' => Person::MALE,
            'F' => Person::FEMALE,
            default => Person::OTHER,
        });
        $this->applyLifeEvent($person, $this->child($record, 'BIRT'), true);
        $this->applyLifeEvent($person, $this->child($record, 'DEAT'), false);
    }

    private function applyLifeEvent(Person $person, ?array $event, bool $birth): void
    {
        $date = \is_array($event) ? $this->value($this->child($event, 'DATE')) : null;
        $place = \is_array($event) ? $this->value($this->child($event, 'PLAC')) : null;
        if ($birth) {
            $person->setBirthGedcomDate($date)->setBirth($this->normalDate($date))->setBirthPlace($place);
        } else {
            $person->setDead(\is_array($event))->setDeathGedcomDate($date)->setDeath($this->normalDate($date))->setDeathPlace($place);
        }
    }

    /** @param array<string, Person> $people @param array<string, GedcomIdentifier> $identifiers @param array<string, int> $created
     * @param array<string, \App\Entity\GedcomIdentifier> $identifiers
     * @param array<string, int> $created */
    private function persistMetadata(GedcomDocument $document, GedcomImport $import, array $people, array $identifiers, array &$created): void
    {
        foreach ($document->records as $record) {
            $tag = $record['tag'];
            if (\in_array($tag, ['NOTE', 'SOUR', 'OBJE'], true)) {
                $type = ['NOTE' => 'note', 'SOUR' => 'source', 'OBJE' => 'media'][$tag];
                $this->entityManager->persist(new GedcomMetadata($import, $type, $record['xref'] ?? null, ['value' => $record['value'], 'line' => $record['line']]));
                ++$created[$type === 'note' ? 'notes' : ($type === 'source' ? 'sources' : 'media')];
            }

            $owner = 'INDI' === $tag ? ($people[$record['xref'] ?? ''] ?? null) : (($identifiers['FAM:' . ($record['xref'] ?? '')] ?? null)?->getUnion());
            if (!$owner instanceof Person && !$owner instanceof Union) {
                continue;
            }

            foreach (['NOTE' => 'note', 'SOUR' => 'citation', 'OBJE' => 'media'] as $metadataTag => $type) {
                foreach ($this->children($record, $metadataTag) as $metadata) {
                    $entity = new GedcomMetadata($import, $type, null, ['value' => $metadata['value'], 'line' => $metadata['line']]);
                    $owner instanceof Person ? $entity->setPerson($owner) : $entity->setUnion($owner);
                    $this->entityManager->persist($entity);
                    ++$created[$type === 'note' ? 'notes' : ($type === 'citation' ? 'sources' : 'media')];
                }
            }
        }
    }

    /** @param list<string> $written */
    private function publishMedia(GedcomDocument $document, GedcomImport $import, array &$written): void
    {
        if ([] === $document->media) {
            return;
        }

        $directory = $this->mediaDirectory . '/' . $import->getId();
        if (!is_dir($directory) && !mkdir($directory, 0o775, true) && !is_dir($directory)) {
            throw new GedcomImportException('GEDZIP media directory cannot be created.');
        }

        foreach ($document->media as $media) {
            $name = basename($media['name']);
            $path = $directory . '/' . sha1($media['name']) . '-' . $name;
            if (false === file_put_contents($path, $media['content'])) {
                throw new GedcomImportException('GEDZIP media file cannot be stored.');
            }

            $written[] = $path;
            $this->entityManager->persist(new GedcomMetadata($import, 'media_file', $media['name'], ['path' => 'pictures/gedcom/' . $import->getId() . '/' . basename($path)]));
        }

        $this->entityManager->flush();
    }

    private function validateReferences(GedcomDocument $document): void
    {
        $ids = [];
        foreach ($document->records as $record) {
            if (isset($record['xref'])) {
                $ids[$record['xref']] = true;
            }
        }

        $visit = static function (array $node) use (&$visit, $ids): void {
            if (\is_string($node['value'] ?? null) && preg_match('/^@[^@\s]+@$/', $node['value']) && !isset($ids[$node['value']])) {
                throw new GedcomImportException(\sprintf('Unknown GEDCOM reference %s at line %d.', $node['value'], $node['line']));
            }

            foreach ($node['children'] ?? [] as $child) {
                $visit($child);
            }
        };
        foreach ($document->records as $record) {
            $visit($record);
        }
    }

    /** @param array<string, mixed> $node @return array<string, mixed>|null */
    private function child(array $node, string $tag): ?array
    {
        foreach ($node['children'] ?? [] as $child) {
            if ($tag === $child['tag']) {
                return $child;
            }
        }

        return null;
    }

    /** @param array<string, mixed> $node @return list<array<string, mixed>> */
    private function children(array $node, string $tag): array
    {
        return array_values(array_filter($node['children'] ?? [], static fn(array $child): bool => $tag === $child['tag']));
    }

    private function value(?array $node): ?string
    {
        return \is_array($node) && '' !== $node['value'] ? (string) $node['value'] : null;
    }

    /**
     * @param non-empty-array<string, mixed> $record
     */
    private function xref(array $record): string
    {
        return isset($record['xref']) && '' !== $record['xref'] ? (string) $record['xref'] : throw new GedcomImportException(\sprintf('%s record at line %d has no identifier.', $record['tag'], $record['line']));
    }

    private function normalDate(?string $date): ?\DateTimeImmutable
    {
        if (null === $date) {
            return null;
        }

        foreach (['!d M Y', '!Y-m-d'] as $format) {
            $parsed = \DateTimeImmutable::createFromFormat($format, strtoupper($date));
            $errors = \DateTimeImmutable::getLastErrors();
            if ($parsed instanceof \DateTimeImmutable && (false === $errors || (0 === $errors['warning_count'] && 0 === $errors['error_count']))) {
                return $parsed;
            }
        }

        return null;
    }

    /** @return array{string, string|null} */
    private function splitName(?string $name): array
    {
        $name = trim((string) $name);
        if (preg_match('/^(.*?)\s*\/([^\/]+)\//', $name, $matches)) {
            return [trim($matches[1]), trim($matches[2])];
        }

        return [$name, null];
    }

    private function treeName(?string $requested, string $filename): string
    {
        return trim((string) $requested) ?: mb_substr(pathinfo($filename, PATHINFO_FILENAME), 0, 30);
    }

    /** @return array<string, int> */
    private function emptyCounters(): array
    {
        return ['people' => 0, 'unions' => 0, 'sources' => 0, 'notes' => 0, 'media' => 0];
    }
}
