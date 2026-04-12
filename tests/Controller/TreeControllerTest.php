<?php

namespace App\Test\Controller;

use App\Entity\Tree;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TreeControllerTest extends WebTestCase
{
    private KernelBrowser $kernelBrowser;

    private EntityManagerInterface $entityManager;

    private EntityRepository $entityRepository;

    private string $path = '/tree/';

    protected function setUp(): void
    {
        $this->kernelBrowser = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->entityRepository = $this->entityManager->getRepository(Tree::class);

        foreach ($this->entityRepository->findAll() as $tree) {
            $this->entityManager->remove($tree);
        }

        $this->entityManager->flush();
    }

    public function testIndex(): void
    {
        $this->kernelBrowser->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Tree index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->kernelBrowser->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->kernelBrowser->submitForm('Save', [
            'tree[createdAt]' => 'Testing',
            'tree[user]' => 'Testing',
        ]);

        self::assertResponseRedirects('/sweet/food/');

        self::assertSame(1, $this->getRepository()->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $tree = new Tree();
        $tree->setCreatedAt('My Title');
        $tree->setUser('My Title');

        $this->entityManager->persist($tree);
        $this->entityManager->flush();

        $this->kernelBrowser->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, sprintf('%s%s', $this->path, $tree->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Tree');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Tree();
        $fixture->setCreatedAt('Value');
        $fixture->setUser('Value');

        $this->entityManager->persist($fixture);
        $this->entityManager->flush();

        $this->kernelBrowser->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->kernelBrowser->submitForm('Update', [
            'tree[createdAt]' => 'Something New',
            'tree[user]' => 'Something New',
        ]);

        self::assertResponseRedirects('/tree/');

        $fixture = $this->entityRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getCreatedAt());
        self::assertSame('Something New', $fixture[0]->getOwner());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $tree = new Tree();
        $tree->setCreatedAt('Value');
        $tree->setUser('Value');

        $this->entityManager->remove($tree);
        $this->entityManager->flush();

        $this->kernelBrowser->request(\Symfony\Component\HttpFoundation\Request::METHOD_GET, sprintf('%s%s', $this->path, $tree->getId()));
        $this->kernelBrowser->submitForm('Delete');

        self::assertResponseRedirects('/tree/');
        self::assertSame(0, $this->entityRepository->count([]));
    }
}
