<?php

declare(strict_types=1);

namespace App\Tests\Service\Tree;

use App\Entity\Person;
use App\Entity\Tree;
use App\Entity\Union;
use App\Service\Tree\AncestorTreeViewModelBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class AncestorTreeViewModelBuilderTest extends TestCase
{
    public function testItBuildsADepthLimitedAncestorTree(): void
    {
        $builder = new AncestorTreeViewModelBuilder(
            new Packages(new Package(new EmptyVersionStrategy())),
            $this->createUrlGenerator(),
        );

        $tree = (new Tree())
            ->setName('Test tree')
            ->setCreatedAt(new \DateTimeImmutable())
        ;

        $grandFather = $this->createPerson(1, $tree, 'John', 'Doe', Person::MALE, null, new \DateTimeImmutable('1940-01-01'));
        $grandMother = $this->createPerson(2, $tree, 'Jane', 'Doe', Person::FEMALE, 'jane.jpg', new \DateTimeImmutable('1942-01-01'));
        $father = $this->createPerson(3, $tree, 'Jack', 'Doe', Person::MALE, null, new \DateTimeImmutable('1970-01-01'));
        $child = $this->createPerson(4, $tree, 'Jill', 'Doe', Person::FEMALE, null, new \DateTimeImmutable('2000-01-01'));

        $grandParentUnion = $this->createUnion(11, $grandFather, $grandMother, $father, new \DateTimeImmutable('1969-04-20'));
        $parentUnion = $this->createUnion(12, $father, $grandMother, $child, new \DateTimeImmutable('1999-05-21'));

        $father->setParentUnion($grandParentUnion);
        $child->setParentUnion($parentUnion);

        $viewModel = $builder->build($child, 2);

        self::assertSame('root', $viewModel->occurrenceId);
        self::assertSame(4, $viewModel->personId);
        self::assertSame('/person/4', $viewModel->profileUrl);
        self::assertSame('2000', $viewModel->yearsLabel);
        self::assertNotNull($viewModel->parentUnion);
        self::assertSame('21/05/1999', $viewModel->parentUnion->startsAtLabel);
        self::assertCount(2, $viewModel->parentUnion->parents);
        self::assertSame(3, $viewModel->parentUnion->parents[0]->personId);
        self::assertSame(2, $viewModel->parentUnion->parents[1]->personId);
        self::assertNull($viewModel->parentUnion->parents[0]->parentUnion);
        self::assertSame('pictures/jane.jpg', $viewModel->parentUnion->parents[1]->portraitUrl);
    }

    public function testItBuildsUnlimitedDepthWhenDepthIsZero(): void
    {
        $builder = new AncestorTreeViewModelBuilder(
            new Packages(new Package(new EmptyVersionStrategy())),
            $this->createUrlGenerator(),
        );

        $tree = (new Tree())
            ->setName('Test tree')
            ->setCreatedAt(new \DateTimeImmutable())
        ;

        $grandFather = $this->createPerson(1, $tree, 'John', 'Doe', Person::MALE, null, new \DateTimeImmutable('1940-01-01'));
        $grandMother = $this->createPerson(2, $tree, 'Jane', 'Doe', Person::FEMALE, null, new \DateTimeImmutable('1942-01-01'));
        $father = $this->createPerson(3, $tree, 'Jack', 'Doe', Person::MALE, null, new \DateTimeImmutable('1970-01-01'));
        $child = $this->createPerson(4, $tree, 'Jill', 'Doe', Person::FEMALE, null, new \DateTimeImmutable('2000-01-01'));

        $grandParentUnion = $this->createUnion(11, $grandFather, $grandMother, $father);
        $parentUnion = $this->createUnion(12, $father, $grandMother, $child);

        $father->setParentUnion($grandParentUnion);
        $child->setParentUnion($parentUnion);

        $viewModel = $builder->build($child, 0);

        self::assertNotNull($viewModel->parentUnion);
        self::assertNotNull($viewModel->parentUnion->parents[0]->parentUnion);
        self::assertSame(1, $viewModel->parentUnion->parents[0]->parentUnion->parents[0]->personId);
        self::assertSame(2, $viewModel->parentUnion->parents[0]->parentUnion->parents[1]->personId);
    }

    private function createPerson(
        int $id,
        Tree $tree,
        string $firstname,
        string $lastname,
        int $gender,
        ?string $portrait,
        ?\DateTimeInterface $birth,
    ): Person {
        $person = (new Person())
            ->setTree($tree)
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setGender($gender)
            ->setPortrait($portrait)
            ->setBirth($birth)
        ;

        $this->setId($person, $id);

        return $person;
    }

    private function createUnion(int $id, Person $parentA, Person $parentB, Person $child, ?\DateTimeImmutable $startsAt = null): Union
    {
        $union = (new Union())
            ->addPerson($parentA)
            ->addPerson($parentB)
            ->addChild($child)
            ->setStartsAt($startsAt)
        ;

        $this->setId($union, $id);

        return $union;
    }

    private function createUrlGenerator(): UrlGeneratorInterface
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->method('generate')
            ->willReturnCallback(static fn (string $route, array $parameters = []): string => match ($route) {
                'app_person_show' => sprintf('/person/%d', $parameters['id']),
                default => '/',
            })
        ;

        return $urlGenerator;
    }

    private function setId(object $entity, int $id): void
    {
        $reflectionProperty = new \ReflectionProperty($entity, 'id');
        $reflectionProperty->setValue($entity, $id);
    }
}
