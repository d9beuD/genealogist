<?php

declare(strict_types=1);

namespace App\Service\Tree;

use Symfony\Component\Serializer\Attribute\Groups;

final readonly class AncestorTreeNodeViewModel
{
    public function __construct(
        #[Groups(['person_tree'])]
        public string $occurrenceId,
        #[Groups(['person_tree'])]
        public int $personId,
        #[Groups(['person_tree'])]
        public string $fullName,
        #[Groups(['person_tree'])]
        public string $profileUrl,
        #[Groups(['person_tree'])]
        public ?string $yearsLabel,
        #[Groups(['person_tree'])]
        public ?string $portraitUrl,
        #[Groups(['person_tree'])]
        public ?int $gender,
        #[Groups(['person_tree'])]
        public ?AncestorTreeUnionViewModel $parentUnion,
    ) {
    }
}
