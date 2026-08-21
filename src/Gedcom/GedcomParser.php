<?php

declare(strict_types=1);

namespace App\Gedcom;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class GedcomParser
{
    private const int MAX_UPLOAD_BYTES = 52_428_800;

    private const int MAX_ARCHIVE_ENTRIES = 200;

    private const int MAX_UNCOMPRESSED_BYTES = 104_857_600;

    public function parse(UploadedFile $file): GedcomDocument
    {
        if (!$file->isValid() || ($file->getSize() ?? 0) > self::MAX_UPLOAD_BYTES) {
            throw new GedcomImportException('The uploaded GEDCOM file is invalid or exceeds the 50 MiB limit.');
        }

        $filename = $file->getClientOriginalName();
        if (str_ends_with(mb_strtolower($filename), '.zip') || str_ends_with(mb_strtolower($filename), '.gdz')) {
            return $this->parseGedzip($file);
        }

        $content = file_get_contents($file->getPathname());
        if (false === $content) {
            throw new GedcomImportException('The uploaded GEDCOM file cannot be read.');
        }

        return $this->parseContent($content, []);
    }

    private function parseGedzip(UploadedFile $file): GedcomDocument
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new GedcomImportException('GEDZIP imports require the PHP zip extension.');
        }

        $zip = new \ZipArchive();
        if (true !== $zip->open($file->getPathname())) {
            throw new GedcomImportException('The uploaded GEDZIP archive cannot be opened.');
        }

        try {
            if ($zip->numFiles > self::MAX_ARCHIVE_ENTRIES) {
                throw new GedcomImportException('The GEDZIP archive has too many entries.');
            }

            $uncompressedSize = 0;
            $media = [];
            $gedcom = null;
            for ($index = 0; $index < $zip->numFiles; ++$index) {
                $stat = $zip->statIndex($index);
                if (false === $stat || !isset($stat['name'], $stat['size'])) {
                    throw new GedcomImportException('The GEDZIP archive contains an invalid entry.');
                }

                $name = $stat['name'];
                $uncompressedSize += $stat['size'];
                if ($uncompressedSize > self::MAX_UNCOMPRESSED_BYTES || str_contains($name, '..') || str_starts_with($name, '/')) {
                    throw new GedcomImportException('The GEDZIP archive violates safety limits.');
                }

                $contents = $zip->getFromIndex($index);
                if (false === $contents) {
                    throw new GedcomImportException('A GEDZIP archive entry cannot be read.');
                }

                if ('gedcom.ged' === $name) {
                    $gedcom = $contents;
                } elseif (!str_ends_with($name, '/')) {
                    $media[] = ['name' => $name, 'content' => $contents];
                }
            }

            if (!\is_string($gedcom)) {
                throw new GedcomImportException('A GEDZIP archive must contain gedcom.ged.');
            }

            return $this->parseContent($gedcom, $media);
        } finally {
            $zip->close();
        }
    }

    /** @param list<array{name: string, content: string}> $media */
    private function parseContent(string $content, array $media): GedcomDocument
    {
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content) ?? $content;
        $roots = [];
        $stack = [];
        foreach (preg_split('/\r\n|\r|\n/', $content) ?: [] as $offset => $line) {
            if ('' === trim($line)) {
                continue;
            }

            if (!preg_match('/^(\d+)\s+(?:(@[^@\s]+@)\s+)?(\w+)(?:\s+(.*))?$/', $line, $matches)) {
                throw new GedcomImportException(\sprintf('Malformed GEDCOM line %d.', $offset + 1));
            }

            $node = ['level' => (int) $matches[1], 'xref' => $matches[2] ?? null, 'tag' => strtoupper($matches[3]), 'value' => $matches[4] ?? '', 'line' => $offset + 1, 'children' => []];
            while ([] !== $stack && $stack[array_key_last($stack)]['level'] >= $node['level']) {
                array_pop($stack);
            }

            if ([] === $stack) {
                $roots[] = $node;
                $stack[] = & $roots[array_key_last($roots)];
            } else {
                $parent = & $stack[array_key_last($stack)];
                $parent['children'][] = $node;
                $stack[] = & $parent['children'][array_key_last($parent['children'])];
                unset($parent);
            }

            unset($node);
        }

        $header = $roots[0] ?? null;
        if (!\is_array($header) || 'HEAD' !== $header['tag']) {
            throw new GedcomImportException('A GEDCOM document must start with a HEAD record.');
        }

        $version = $this->childValue($this->child($header, 'GEDC') ?? [], 'VERS');
        $format = match (true) {
            '5.5' === $version => '5.5',
            \is_string($version) && preg_match('/^7\.0(?:\.\d+)?$/', $version) => '7.0',
            default => throw new GedcomImportException(\sprintf('Unsupported GEDCOM version "%s".', $version ?? 'unknown')),
        };
        if ([] === $roots || 'TRLR' !== ($roots[array_key_last($roots)]['tag'] ?? null)) {
            throw new GedcomImportException('A GEDCOM document must end with a TRLR record.');
        }

        $warnings = [];
        foreach ($roots as $record) {
            if (!\in_array($record['tag'], ['HEAD', 'TRLR', 'INDI', 'FAM', 'NOTE', 'SOUR', 'OBJE'], true)) {
                $warnings[] = ['code' => 'unsupported_tag', 'line' => $record['line'], 'tag' => $record['tag'], 'message' => 'Unsupported GEDCOM record was ignored.'];
            }
        }

        return new GedcomDocument($format, $roots, $media, $warnings);
    }

    /** @param array<string, mixed> $node @return array<string, mixed>|null */
    private function child(array $node, string $tag): ?array
    {
        foreach ($node['children'] ?? [] as $child) {
            if ($tag === $child['tag']) {
                return $child;
            }
        }

        return null;
    }

    /** @param array<string, mixed> $node */
    private function childValue(array $node, string $tag): ?string
    {
        $child = $this->child($node, $tag);

        return \is_array($child) ? (string) $child['value'] : null;
    }
}
