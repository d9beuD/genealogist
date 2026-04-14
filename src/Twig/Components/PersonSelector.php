<?php

namespace App\Twig\Components;

use App\Entity\Person;
use App\Entity\Tree;
use App\Repository\PersonRepository;
use DateTimeImmutable;
use Symfony\Component\Form\FormView;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent()]
final class PersonSelector
{
    use DefaultActionTrait;

    public FormView $formView;
    public Tree $tree;
    public ?Person $personToFilterFrom = null;
    public string $placeholder = '';
    public string $id = '';

    public function __construct(
        private readonly PersonRepository $personRepository,
    ) {
    }

    /**
     * @return list<Person>
     */
    public function getPeople(): array
    {
        $queryBuilder = $this->personRepository->createQueryBuilder('p');
        $query = $queryBuilder
            ->select('p', 'COALESCE(p.birthName, p.lastname) as HIDDEN lastName')
            ->where('p.tree = :tree')
            ->setParameter('tree', $this->tree)
            ->orderBy('lastName', 'ASC')
            ->addOrderBy('p.firstname', 'ASC')
            ->getQuery()
        ;

        /** @var list<Person> */
        $people = $query->getResult();

        if (!$this->personToFilterFrom instanceof Person) {
            return $people;
        }

        // Search for someone who was alive during personToFilterFrom's life
        if ($this->personToFilterFrom->getBirth() instanceof DateTimeImmutable) {
            $people = array_filter(
                $people,
                fn (Person $person): bool
                    => $person->getDeath() ? $person->getDeath() > $this->personToFilterFrom->getBirth() : true
            );
        }

        if ($this->personToFilterFrom->getDeath() instanceof DateTimeImmutable) {
            $people = array_filter(
                $people,
                fn (Person $person): bool
                    => $person->getBirth() ? $person->getBirth() < $this->personToFilterFrom->getDeath() : true
            );
        }

        return $people;
    }
}
