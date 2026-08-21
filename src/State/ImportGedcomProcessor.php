<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Application\Gedcom\ImportGedcom;
use App\Dto\GedcomImportOutput;
use App\Dto\ImportGedcomInput;
use App\Entity\Tree;
use App\Entity\User;
use App\Gedcom\GedcomImportException;
use App\Gedcom\GedcomParser;
use App\Repository\TreeRepository;
use App\Security\Voter\TreeVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpFoundation\RequestStack;

/** @implements ProcessorInterface<ImportGedcomInput, GedcomImportOutput> */
final readonly class ImportGedcomProcessor implements ProcessorInterface
{
    public function __construct(private Security $security, private TreeRepository $trees, private GedcomParser $parser, private ImportGedcom $importGedcom, private RequestStack $requestStack) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): GedcomImportOutput
    {
        $request = $this->requestStack->getCurrentRequest();
        $file = $request?->files->get('file');
        if (!$file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
            throw new BadRequestHttpException('A GEDCOM file is required.');
        }

        $treeId = $request?->request->get('treeId');
        $treeName = $request?->request->get('treeName');
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $tree = null;
        if (null !== $treeId && '' !== $treeId) {
            $tree = $this->trees->find((int) $treeId);
            if (!$tree instanceof Tree) {
                throw new NotFoundHttpException('Tree not found.');
            }

            if (!$this->security->isGranted(TreeVoter::EDIT, $tree)) {
                throw new AccessDeniedHttpException();
            }
        }

        try {
            return ($this->importGedcom)($this->parser->parse($file), $user, $file->getClientOriginalName(), $tree, \is_string($treeName) ? $treeName : null);
        } catch (GedcomImportException $exception) {
            throw new UnprocessableEntityHttpException($exception->getMessage(), $exception);
        }
    }
}
