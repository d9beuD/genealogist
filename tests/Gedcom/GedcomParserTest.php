<?php

declare(strict_types=1);

namespace App\Tests\Gedcom;

use App\Gedcom\GedcomImportException;
use App\Gedcom\GedcomParser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class GedcomParserTest extends TestCase
{
    public function testParsesGedcom55And70Fixtures(): void
    {
        $parser = new GedcomParser();

        self::assertSame('5.5', $parser->parse($this->file('family-5.5.ged'))->format);
        self::assertSame('7.0', $parser->parse($this->file('family-7.0.ged'))->format);
    }

    public function testRejectsUnknownVersion(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'gedcom-');
        self::assertNotFalse($path);
        file_put_contents($path, "0 HEAD\n1 GEDC\n2 VERS 5.5.1\n0 TRLR\n");

        $this->expectException(GedcomImportException::class);
        new GedcomParser()->parse(new UploadedFile($path, 'unsupported.ged', null, null, true));
    }

    public function testParsesGedzipWithEmbeddedMedia(): void
    {
        if (!class_exists(\ZipArchive::class)) {
            self::markTestSkipped('The zip extension is unavailable.');
        }

        $path = tempnam(sys_get_temp_dir(), 'gedzip-');
        self::assertNotFalse($path);
        $zip = new \ZipArchive();
        self::assertTrue($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE));
        $zip->addFromString('gedcom.ged', (string) file_get_contents(__DIR__ . '/../Fixtures/Gedcom/family-7.0.ged'));
        $zip->addFromString('media/portrait.txt', 'portrait');
        $zip->close();

        $document = new GedcomParser()->parse(new UploadedFile($path, 'family.zip', 'application/zip', null, true));

        self::assertSame('7.0', $document->format);
        self::assertCount(1, $document->media);
        self::assertSame('media/portrait.txt', $document->media[0]['name']);
    }

    private function file(string $name): UploadedFile
    {
        return new UploadedFile(__DIR__ . '/../Fixtures/Gedcom/' . $name, $name, 'application/octet-stream', null, true);
    }
}
