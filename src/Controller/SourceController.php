<?php

namespace App\Controller;

use App\Entity\Person;
use App\Entity\Source;
use App\Form\SourceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/person/{personId}/source')]
class SourceController extends AbstractController
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    #[Route('/', name: 'app_source_index', methods: ['GET', 'POST'])]
    #[IsGranted('edit', 'person')]
    public function index(Request $request, EntityManagerInterface $entityManager, #[MapEntity(id: 'personId')] Person $person): Response
    {
        $source = new Source();
        $createForm = $this->createForm(SourceType::class, $source);
        $createForm->handleRequest($request);

        if ($createForm->isSubmitted() && $createForm->isValid()) {
            $source->setPerson($person);
            $entityManager->persist($source);
            $entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('source.new.success')
            );
            return $this->redirectToRoute('app_source_index', ['personId' => $person->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('source/index.html.twig', [
            'sources' => $person->getSources(),
            'person' => $person,
            'create_form' => $createForm->createView(),
            'edit_form' => null,
            'editing_source' => null,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_source_edit', methods: ['GET', 'POST'])]
    #[IsGranted('edit', 'person')]
    public function edit(
        Request $request,
        Source $source,
        EntityManagerInterface $entityManager,
        #[MapEntity(id: 'personId')]
        Person $person,
    ): Response {
        $this->assertSourceBelongsToPerson($source, $person);

        $createForm = $this->createForm(SourceType::class, new Source());
        $editForm = $this->createForm(SourceType::class, $source);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('source.edit.success')
            );

            return $this->redirectToRoute('app_source_index', ['personId' => $person->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('source/index.html.twig', [
            'sources' => $person->getSources(),
            'person' => $person,
            'create_form' => $createForm->createView(),
            'edit_form' => $editForm->createView(),
            'editing_source' => $source,
        ]);
    }

    #[Route('/{id}', name: 'app_source_delete', methods: ['POST'])]
    #[IsGranted('edit', 'person')]
    public function delete(Request $request, Source $source, EntityManagerInterface $entityManager, #[MapEntity(id: 'personId')] Person $person): Response
    {
        $this->assertSourceBelongsToPerson($source, $person);

        if ($this->isCsrfTokenValid('delete'.$source->getId(), $request->request->get('_token'))) {
            $entityManager->remove($source);
            $entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('source.delete.success')
            );
        } else {
            $this->addFlash(
                'danger',
                $this->translator->trans('source.delete.error')
            );
        }

        return $this->redirectToRoute('app_source_index', ['personId' => $person->getId()], Response::HTTP_SEE_OTHER);
    }

    private function assertSourceBelongsToPerson(Source $source, Person $person): void
    {
        if ($source->getPerson()?->getId() !== $person->getId()) {
            throw $this->createNotFoundException();
        }
    }
}
