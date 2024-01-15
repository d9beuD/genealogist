<?php

namespace App\Controller;

use App\Entity\Person;
use App\Entity\Tree;
use App\Form\PersonType;
use App\Service\ImageManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tree/{treeId}/person')]
class PersonController extends AbstractController
{
    #[Route('/new', name: 'app_person_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request, 
        EntityManagerInterface $entityManager,
        #[MapEntity(id: 'treeId')] Tree $tree,
        ImageManager $imageManager,
    ): Response 
    {
        $person = new Person();
        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('portrait')->getData()) {
                $path = $imageManager->save($form->get('portrait')->getData(), $request);
                $person->setPortrait($path);
            }
            $tree->addMember($person);
            $entityManager->persist($person);
            $entityManager->flush();

            return $this->redirectToRoute('app_tree_show', ['id' => $tree->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('person/new.html.twig', [
            'person' => $person,
            'form' => $form,
            'tree' => $tree,
        ]);
    }

    #[Route('/{id}', name: 'app_person_show', methods: ['GET'])]
    public function show(Person $person): Response
    {
        return $this->render('person/show.html.twig', [
            'person' => $person,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_person_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request, 
        Person $person, 
        EntityManagerInterface $entityManager,
        #[MapEntity(id: 'treeId')] Tree $tree,
        ImageManager $imageManager,
    ): Response
    {
        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('portrait')->getData()) {
                $path = $imageManager->save($form->get('portrait')->getData(), $request);
                $imageManager->remove($person->getPortrait());
                $person->setPortrait($path);
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_tree_show', ['id' => $tree->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('person/edit.html.twig', [
            'person' => $person,
            'form' => $form,
            'tree' => $tree,
        ]);
    }

    #[Route('/{id}', name: 'app_person_delete', methods: ['POST'])]
    public function delete(Request $request, Person $person, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$person->getId(), $request->request->get('_token'))) {
            $entityManager->remove($person);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_person_index', [], Response::HTTP_SEE_OTHER);
    }
}
