<?php

namespace App\Controller;

use App\Entity\Person;
use App\Entity\User;
use App\Form\PersonType;
use App\Form\TreeOptionsType;
use App\Service\FavoriteMemberManager;
use App\Service\ImageManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class PersonController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator, private readonly EntityManagerInterface $entityManager, private readonly ImageManager $imageManager, private readonly FavoriteMemberManager $favoriteMemberManager,
    ) {
    }

    #[Route('/person/{id}', name: 'app_person_show', methods: ['GET'])]
    #[IsGranted('view', 'person')]
    public function show(Person $person): Response
    {
        return $this->render('person/show.html.twig', [
            'person' => $person,
        ]);
    }

    #[Route('/person/{id}/unions', name: 'app_person_unions', methods: ['GET'])]
    #[IsGranted('edit', 'person')]
    public function unions(Person $person): Response
    {
        return $this->render('person/unions.html.twig', [
            'person' => $person,
        ]);
    }

    #[Route('/person/{id}/edit', name: 'app_person_edit', methods: ['GET', 'POST'])]
    #[IsGranted('edit', 'person')]
    public function edit(
        Request $request,
        Person $person,
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
                $path = $this->imageManager->save($form->get('portrait')->getData(), $request);
                if ($person->getPortrait()) {
                    $this->imageManager->remove($person->getPortrait());
                }

                $person->setPortrait($path);
            }

            $this->entityManager->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('person.edit.success', ['name' => $person->getFullName()]),
            );

            return $this->redirectToRoute('app_person_show', ['id' => $person->getId()]);
        }

        return $this->render('person/edit.html.twig', [
            'person' => $person,
            'form' => $form,
            'tree' => $person->getTree(),
        ]);
    }

    #[Route('/person/{id}', name: 'app_person_delete', methods: ['POST'])]
    #[IsGranted('delete', 'person')]
    public function delete(
        Request $request,
        Person $person,
    ): Response {
        $tree = $person->getTree();

        if ($this->isCsrfTokenValid('delete'.$person->getId(), $request->request->get('_token'))) {
            if ($person->getPortrait()) {
                $this->imageManager->remove($person->getPortrait());
            }

            $this->entityManager->remove($person);
            $this->entityManager->flush();
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

    #[Route('/person/{id}/tree', name: 'app_person_tree', methods: ['GET'])]
    #[IsGranted('view', 'person')]
    public function tree(Person $person, Request $request): Response
    {
        $form = $this->createForm(TreeOptionsType::class);
        $form->handleRequest($request);

        return $this->render('person/show_tree.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
            'tree_view' => true,
        ]);
    }

    #[Route('/person/{id}/favorite', name: 'app_person_favorite', methods: [Request::METHOD_GET])]
    public function favorite(Person $person, #[CurrentUser()] User $user): Response
    {
        $this->favoriteMemberManager->favorite($person, $user);

        $this->addFlash(
            'success',
            $this->translator->trans('person.favorite.added', ['name' => $person->getFullName()]),
        );

        return $this->redirectToRoute('app_tree_show', ['id' => $person->getTree()->getId()]);
    }

    #[Route('/person/{id}/unfavorite', name: 'app_person_unfavorite', methods: [Request::METHOD_GET])]
    public function unfavorite(Person $person, #[CurrentUser()] User $user): Response
    {
        $this->favoriteMemberManager->unfavorite($person, $user);

        $this->addFlash(
            'success',
            $this->translator->trans('person.favorite.removed', ['name' => $person->getFullName()]),
        );

        return $this->redirectToRoute('app_tree_show', ['id' => $person->getTree()->getId()]);
    }
}
