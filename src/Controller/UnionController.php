<?php

namespace App\Controller;

use App\Entity\Person;
use App\Entity\Union;
use App\Form\PersonSelectType;
use App\Form\UnionType;
use App\Repository\UnionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UnionController extends AbstractController
{
    #[Route('/union', name: 'app_union_index', methods: ['GET'])]
    public function index(UnionRepository $unionRepository): Response
    {
        return $this->render('union/index.html.twig', [
            'unions' => $unionRepository->findAll(),
        ]);
    }

    #[Route('/person/{personId}/union/new', name: 'app_union_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, #[MapEntity(id: 'personId')] Person $person): Response
    {
        $union = new Union();
        $form = $this->createForm(UnionType::class, $union);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $person->addUnion($union);
            $entityManager->persist($union);
            $entityManager->flush();

            return $this->redirectToRoute('app_union_edit', [
                'personId' => $person->getId(),
                'id' => $union->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('union/new.html.twig', [
            'union' => $union,
            'form' => $form,
            'person' => $person,
        ]);
    }

    #[Route('/union/{id}', name: 'app_union_show', methods: ['GET'])]
    public function show(Union $union): Response
    {
        return $this->render('union/show.html.twig', [
            'union' => $union,
        ]);
    }
    
    #[Route('/person/{personId}/union/{id}/edit', name: 'app_union_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Union $union, EntityManagerInterface $entityManager, #[MapEntity(id: 'personId')] Person $person): Response
    {
        $form = $this->createForm(UnionType::class, $union);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            
            return $this->redirectToRoute('app_union_edit', [
                'personId' => $person->getId(),
                'id' => $union->getId(),
            ], Response::HTTP_SEE_OTHER);
        }
        
        return $this->render('union/edit.html.twig', [
            'union' => $union,
            'form' => $form,
            'person' => $person,
            'personForm' => $this->createForm(PersonSelectType::class, null, [
                'current_tree' => $person->getTree(),
                'members_to_exclude' => $union->getPeople(),
            ])
        ]);
    }

    #[Route('/union/{id}', name: 'app_union_delete', methods: ['POST'])]
    public function delete(Request $request, Union $union, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$union->getId(), $request->request->get('_token'))) {
            $entityManager->remove($union);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_union_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/person/{personId}/union/{id}/add-partner', name: 'app_union_add_partner', methods: ['POST'])]
    public function addPartner(Request $request, EntityManagerInterface $entityManager, #[MapEntity(id: 'personId')] Person $person, Union $union): Response
    {
        $form = $this->createForm(PersonSelectType::class, null, [
            'current_tree' => $person->getTree(),
            'members_to_exclude' => $union->getPeople(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $union->addPerson($form->get('person')->getData());
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_union_edit', [
            'personId' => $person->getId(),
            'id' => $union->getId(),
        ], Response::HTTP_SEE_OTHER);
    }

    #[Route('/person/{personId}/union/{id}/remove-partner/{partnerId}', name: 'app_union_remove_partner', methods: ['POST'])]
    public function removePartner(
        Request $request, 
        EntityManagerInterface $entityManager, 
        #[MapEntity(id: 'personId')] 
        Person $person, 
        Union $union, 
        #[MapEntity(id: 'partnerId')] 
        Person $partner
    ): Response
    {
        if ($this->isCsrfTokenValid('delete'.$partner->getId(), $request->request->get('_token'))) {
            $union->removePerson($partner);

            if ($union->getPeople()->count() === 0) {
                $entityManager->remove($union);
                $entityManager->flush();

                return $this->redirectToRoute('app_person_edit', [
                    'treeId' => $person->getTree()->getId(),
                    'id' => $person->getId(),
                ], Response::HTTP_SEE_OTHER);
            }

            $entityManager->flush();
        }

        return $this->redirectToRoute('app_union_edit', [
            'personId' => $person->getId(),
            'id' => $union->getId(),
        ], Response::HTTP_SEE_OTHER);
    }
}
