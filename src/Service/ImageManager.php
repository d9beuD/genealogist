<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImageManager
{
    public function __construct(
        private readonly SluggerInterface $slugger,
        private readonly ParameterBagInterface $params,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function save(UploadedFile $uploadedFile, ?Request $request = null): ?string
    {
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessClientExtension();

        try {
            $uploadedFile->move(
                $this->params->get('portraits_directory'),
                $newFilename
            );
        } catch (FileException $fileException) {
            $this->logger->error($fileException->getMessage());
        }

        return $newFilename;
    }

    public function update(string $oldFilename, UploadedFile $uploadedFile): ?string
    {
        $this->remove($oldFilename);

        return $this->save($uploadedFile);
    }

    public function remove(string $filename): void
    {
        $filePath = $this->params->get('portraits_directory').'/'.$filename;

        if (file_exists($filePath)) {
            try {
                unlink($filePath);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                // $this->flashBag->add('danger', 'An error occurred while removing the image.');
            }
        }

        // $this->flashBag->add('warning', 'The image does not exist.');
    }
}
