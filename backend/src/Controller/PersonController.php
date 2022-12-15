<?php

namespace App\Controller;

use App\Entity\Person;
use App\Entity\Tree;
use App\Entity\User;
use App\Repository\PersonRepository;
use App\Service\PaginationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class PersonController extends AbstractController
{
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
            ->setGender($request->get('gender'))
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
        if ($person->getTree()->getOwner()->getUserIdentifier() !== $this->getUser()->getUserIdentifier()) {
            throw new AccessDeniedException();
        }

        return $this->json($person);
    }

    #[Route('/person/{id}', name: 'app_person_edit', methods: ['PUT'])]
    public function edit(Request $request, Person $person, PersonRepository $personRepository): Response
    {
        if ($person->getTree()->getOwner()->getUserIdentifier() !== $this->getUser()->getUserIdentifier()) {
            throw new AccessDeniedException();
        }

        $person
            ->setBirthDate($request->get('birthDate') ? new \DateTime($request->get('birthDate')) : null)
            ->setBirthName($request->get('birthName'))
            ->setDeathDate($request->get('deathDate') ? new \DateTime($request->get('deathDate')) : null)
            ->setDescription($request->get('description'))
            ->setFirstname($request->get('firstname'))
            ->setLastname($request->get('lastname'))
            ->setPicture($request->get('picture'))
            ->setGender($request->get('gender'))
            ->setIsBirthDateCertain($request->get('isBirthDateCertain'))
            ->setIsBirthDateKnown($request->get('isBirthDateKnown'))
            ->setIsDeathDateCertain($request->get('isDeathDateCertain'))
            ->setIsDeathDateKnown($request->get('isDeathDateKnown'));
        $personRepository->save($person, true);

        return $this->json($person);
    }

    #[Route('/person/{id}', name: 'app_person_delete', methods: ['DELETE'])]
    public function delete(Person $person, PersonRepository $personRepository): Response
    {
        if ($person->getTree()->getOwner()->getUserIdentifier() !== $this->getUser()->getUserIdentifier()) {
            throw new AccessDeniedException();
        }

        $personRepository->remove($person, true);

        return $this->json($person);
    }

    #[Route('/person/{id}/picture', name: 'app_person_picture', methods: ['POST'])]
    public function setPicture(
        Request $request,
        Person $person,
        PersonRepository $personRepository,
    ): Response {
        if ($person->getTree()->getOwner()->getUserIdentifier() !== $this->getUser()->getUserIdentifier()) {
            throw new AccessDeniedException();
        }

        /** @var UploadedFile */
        $picture = $request->files->get('picture');

        if ($picture === null) {
            throw new BadRequestHttpException('Missing picture');
        }

        $tmp_path = $picture->getPathname();
        $name = uniqid() . '-' . $picture->getClientOriginalName();
        @mkdir('public/images', recursive: true);
        move_uploaded_file($tmp_path, 'public/images/' . $name);

        if ($person->getPicture()) {
            @unlink($person->getPicture());
        }

        $person->setPicture('public/images/' . $name);
        $personRepository->save($person, true);

        return $this->json($person);
    }

    #[Route('/person/{id}/picture', name: 'app_person_picture_remove', methods: ['DELETE'])]
    public function removePicture(
        Person $person,
        PersonRepository $personRepository,
    ): Response {
        if ($person->getTree()->getOwner()->getUserIdentifier() !== $this->getUser()->getUserIdentifier()) {
            throw new AccessDeniedException();
        }

        if ($person->getPicture()) {
            unlink($person->getPicture());
        }

        $person->setPicture(null);
        $personRepository->save($person, true);

        return $this->json($person);
    }
}
