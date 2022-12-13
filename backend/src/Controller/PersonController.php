<?php

namespace App\Controller;

use App\Entity\Person;
use App\Entity\Tree;
use App\Entity\User;
use App\Form\PersonType;
use App\Repository\PersonRepository;
use App\Service\PaginationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class PersonController extends AbstractController
{
    #[Route('/tree/{id}/members', name: 'app_tree_members', methods: ['GET'])]
    public function getTreeMembers(Request $request, Tree $tree, PersonRepository $personRepository): Response
    {
        return $this->json([
            'data' => $personRepository->findTreeMembers($tree, $request),
            'count' => $personRepository->findTreeMembersCount($tree),
            'limit' => PaginationService::getLimit($request),
            'offset' => PaginationService::getOffset($request),
        ]);
    }

    #[Route('/tree/{id}/members', name: 'app_person_new', methods: ['POST'])]
    public function new(
        Request $request,
        Tree $tree,
        PersonRepository $personRepository,
        #[CurrentUser] User $user
    ): Response {
        if ($tree->getOwner()->getUserIdentifier() !== $user->getUserIdentifier()) {
            throw new AccessDeniedException();
        }

        $person = new Person();
        $person
            ->setBirthDate($request->get('birthDate') ? new \DateTime($request->get('birthDate')) : null)
            ->setBirthName($request->get('birthName'))
            ->setDeathDate($request->get('deathDate') ? new \DateTime($request->get('deathDate')) : null)
            ->setDescription($request->get('description'))
            ->setFirstname($request->get('firstname'))
            ->setLastname($request->get('lastname'))
            ->setPicture($request->get('picture'))
            ->setIsBirthDateCertain($request->get('isBirthDateCertain'))
            ->setIsBirthDateKnown($request->get('isBirthDateKnown'))
            ->setIsDeathDateCertain($request->get('isDeathDateCertain'))
            ->setIsDeathDateKnown($request->get('isDeathDateKnown'));

        $tree->addMember($person);

        $personRepository->save($person, true);
        return $this->json($person);
    }

    #[Route('/person/{id}', name: 'app_person_show', methods: ['GET'])]
    public function show(Person $person): Response
    {
        return $this->render('person/show.html.twig', [
            'person' => $person,
        ]);
    }

    #[Route('/person/{id}/edit', name: 'app_person_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Person $person, PersonRepository $personRepository): Response
    {
        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $personRepository->save($person, true);

            return $this->redirectToRoute('app_person_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('person/edit.html.twig', [
            'person' => $person,
            'form' => $form,
        ]);
    }

    #[Route('/person/{id}', name: 'app_person_delete', methods: ['POST'])]
    public function delete(Request $request, Person $person, PersonRepository $personRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $person->getId(), $request->request->get('_token'))) {
            $personRepository->remove($person, true);
        }

        return $this->redirectToRoute('app_person_index', [], Response::HTTP_SEE_OTHER);
    }
}
