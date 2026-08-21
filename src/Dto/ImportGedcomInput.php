<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class ImportGedcomInput
{
    public ?UploadedFile $file = null;

    public ?int $treeId = null;

    public ?string $treeName = null;
}
