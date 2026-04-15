<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Person;
use App\Service\UnionMemberSelector;
use Symfony\Component\Form\FormView;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent()]
final class PersonSelector
{
    public int $treeId;
    public FormView $field;
    public string $modelName;
    public ?int $selectedPersonId = null;
    public ?int $personToFilterFromId = null;
    public ?int $excludedPersonId = null;
    public string $placeholder = '';

    public function __construct(
        private readonly UnionMemberSelector $unionMemberSelector,
    ) {
    }

    public function getInputId(): string
    {
        return $this->field->vars['id'].'_autocomplete';
    }

    /**
     * @return list<Person>
     */
    public function getPeople(): array
    {
        return $this->unionMemberSelector->getSelectablePeople(
            $this->treeId,
            $this->personToFilterFromId,
            $this->excludedPersonId,
        );
    }
}
