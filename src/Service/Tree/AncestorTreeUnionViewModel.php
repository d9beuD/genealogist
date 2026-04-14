<?php

declare(strict_types=1);

namespace App\Service\Tree;

use Symfony\Component\Serializer\Attribute\Groups;

final readonly class AncestorTreeUnionViewModel
{
    /**
     * @param list<AncestorTreeNodeViewModel> $parents
     */
    public function __construct(
        #[Groups(['person_tree'])]
        public int $unionId,
        #[Groups(['person_tree'])]
        public ?string $startsAtLabel,
        #[Groups(['person_tree'])]
        public array $parents,
    ) {
    }
}
