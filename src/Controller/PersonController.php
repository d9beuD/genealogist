<?php

namespace App\Controller;

use App\Entity\Person;
use App\Form\PersonType;
use App\Service\ImageManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;


#[Route('/person')]
class PersonController extends AbstractController
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {}

    #[Route('/{id}', name: 'app_person_show', methods: ['GET'])]
    #[IsGranted('view', 'person')]
    public function show(Person $person): Response
    {
        return $this->render('person/show.html.twig', [
            'person' => $person,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_person_edit', methods: ['GET', 'POST'])]
    #[IsGranted('edit', 'person')]
    public function edit(
        Request $request,
        Person $person,
        EntityManagerInterface $entityManager,
        ImageManager $imageManager,
    ): Response {
        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$person->isDead()) {
                $person->setDeath(null);
                $person->setDeathDayUnsure(false);
                $person->setDeathMonthUnsure(false);
                $person->setDeathYearUnsure(false);
            }

            if ($form->get('portrait')->getData()) {
                $path = $imageManager->save($form->get('portrait')->getData(), $request);
                if ($person->getPortrait()) {
                    $imageManager->remove($person->getPortrait());
                }
                $person->setPortrait($path);
            }

            $entityManager->flush();
            $this->addFlash(
                'success',
                $this->translator->trans('person.edit.success', ['name' => $person->getFullName()]),
            );

            return $this->redirectToRoute('app_tree_show', ['id' => $person->getTree()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('person/edit.html.twig', [
            'person' => $person,
            'form' => $form,
            'tree' => $person->getTree(),
        ]);
    }

    #[Route('/{id}', name: 'app_person_delete', methods: ['POST'])]
    #[IsGranted('delete', 'person')]
    public function delete(
        Request $request,
        Person $person,
        EntityManagerInterface $entityManager,
        ImageManager $imageManager,
    ): Response {
        $tree = $person->getTree();

        if ($this->isCsrfTokenValid('delete' . $person->getId(), $request->request->get('_token'))) {
            if ($person->getPortrait()) {
                $imageManager->remove($person->getPortrait());
            }
            $entityManager->remove($person);
            $entityManager->flush();
            $this->addFlash(
                'success',
                $this->translator->trans('person.delete.success', ['name' => $person->getFullName()]),
            );
        } else {
            $this->addFlash(
                'danger',
                $this->translator->trans('person.delete.error', ['name' => $person->getFullName()]),
            );
        }

        return $this->redirectToRoute('app_tree_show', ['id' => $tree->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/tree', name: 'app_person_tree', methods: ['GET'])]
    #[IsGranted('view', 'person')]
    public function tree(Person $person): Response
    {
        return $this->render('person/show_tree.html.twig', [
            'person' => $person,
        ]);
    }
}
