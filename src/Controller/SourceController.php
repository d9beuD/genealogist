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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/person/{personId}/source')]
class SourceController extends AbstractController
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {}
    
    #[Route('/', name: 'app_source_index', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager, #[MapEntity(id: 'personId')] Person $person): Response
    {
        $source = new Source();
        $form = $this->createForm(SourceType::class, $source);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_source_delete', methods: ['POST'])]
    public function delete(Request $request, Source $source, EntityManagerInterface $entityManager, #[MapEntity(id: 'personId')] Person $person): Response
    {
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
}
