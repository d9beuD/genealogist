<?php

namespace App\Controller;

use App\Entity\Person;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class LifeLineController extends AbstractController
{
    #[Route('/person/{id}/life-line', name: 'app_person_life_line')]
    public function index(Person $person, TranslatorInterface $translator): Response
    {
    }
}
