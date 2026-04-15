<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Service\UnionMemberSelector;
use Symfony\Component\Form\FormView;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class UnionMemberSelectors
{
    use DefaultActionTrait;

    #[LiveProp]
    public int $treeId;

    #[LiveProp]
    public string $member1Placeholder = '';

    #[LiveProp]
    public string $member2Placeholder = '';

    public FormView $member1Field;

    public FormView $member2Field;

    #[LiveProp(writable: true, onUpdated: 'onMember1Updated')]
    public ?int $member1Id = null;

    #[LiveProp(writable: true, onUpdated: 'onMember2Updated')]
    public ?int $member2Id = null;

    public function __construct(
        private readonly UnionMemberSelector $unionMemberSelector,
    ) {
    }

    public function onMember1Updated(): void
    {
        if (null === $this->member2Id) {
            return;
        }

        if (!$this->unionMemberSelector->isSelectable($this->treeId, $this->member2Id, $this->member1Id, $this->member1Id)) {
            $this->member2Id = null;
        }
    }

    public function onMember2Updated(): void
    {
        if (null === $this->member2Id) {
            return;
        }

        if (!$this->unionMemberSelector->isSelectable($this->treeId, $this->member2Id, $this->member1Id, $this->member1Id)) {
            $this->member2Id = null;
        }
    }
}
