<?php

namespace App\Test\Controller;

use App\Entity\Person;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PersonControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/person/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Person::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Person index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'person[firstname]' => 'Testing',
            'person[lastname]' => 'Testing',
            'person[birth]' => 'Testing',
            'person[death]' => 'Testing',
            'person[birthDaySure]' => 'Testing',
            'person[birthMonthSure]' => 'Testing',
            'person[birthYearSure]' => 'Testing',
            'person[deathDaySure]' => 'Testing',
            'person[deathMonthSure]' => 'Testing',
            'person[deathYearSure]' => 'Testing',
            'person[portrait]' => 'Testing',
            'person[bio]' => 'Testing',
            'person[unions]' => 'Testing',
            'person[parentUnion]' => 'Testing',
            'person[tree]' => 'Testing',
        ]);

        self::assertResponseRedirects('/sweet/food/');

        self::assertSame(1, $this->getRepository()->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Person();
        $fixture->setFirstname('My Title');
        $fixture->setLastname('My Title');
        $fixture->setBirth('My Title');
        $fixture->setDeath('My Title');
        $fixture->setBirthDaySure('My Title');
        $fixture->setBirthMonthSure('My Title');
        $fixture->setBirthYearSure('My Title');
        $fixture->setDeathDaySure('My Title');
        $fixture->setDeathMonthSure('My Title');
        $fixture->setDeathYearSure('My Title');
        $fixture->setPortrait('My Title');
        $fixture->setBio('My Title');
        $fixture->setUnions('My Title');
        $fixture->setParentUnion('My Title');
        $fixture->setTree('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Person');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Person();
        $fixture->setFirstname('Value');
        $fixture->setLastname('Value');
        $fixture->setBirth('Value');
        $fixture->setDeath('Value');
        $fixture->setBirthDaySure('Value');
        $fixture->setBirthMonthSure('Value');
        $fixture->setBirthYearSure('Value');
        $fixture->setDeathDaySure('Value');
        $fixture->setDeathMonthSure('Value');
        $fixture->setDeathYearSure('Value');
        $fixture->setPortrait('Value');
        $fixture->setBio('Value');
        $fixture->setUnions('Value');
        $fixture->setParentUnion('Value');
        $fixture->setTree('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'person[firstname]' => 'Something New',
            'person[lastname]' => 'Something New',
            'person[birth]' => 'Something New',
            'person[death]' => 'Something New',
            'person[birthDaySure]' => 'Something New',
            'person[birthMonthSure]' => 'Something New',
            'person[birthYearSure]' => 'Something New',
            'person[deathDaySure]' => 'Something New',
            'person[deathMonthSure]' => 'Something New',
            'person[deathYearSure]' => 'Something New',
            'person[portrait]' => 'Something New',
            'person[bio]' => 'Something New',
            'person[unions]' => 'Something New',
            'person[parentUnion]' => 'Something New',
            'person[tree]' => 'Something New',
        ]);

        self::assertResponseRedirects('/person/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getFirstname());
        self::assertSame('Something New', $fixture[0]->getLastname());
        self::assertSame('Something New', $fixture[0]->getBirth());
        self::assertSame('Something New', $fixture[0]->getDeath());
        self::assertSame('Something New', $fixture[0]->getBirthDaySure());
        self::assertSame('Something New', $fixture[0]->getBirthMonthSure());
        self::assertSame('Something New', $fixture[0]->getBirthYearSure());
        self::assertSame('Something New', $fixture[0]->getDeathDaySure());
        self::assertSame('Something New', $fixture[0]->getDeathMonthSure());
        self::assertSame('Something New', $fixture[0]->getDeathYearSure());
        self::assertSame('Something New', $fixture[0]->getPortrait());
        self::assertSame('Something New', $fixture[0]->getBio());
        self::assertSame('Something New', $fixture[0]->getUnions());
        self::assertSame('Something New', $fixture[0]->getParentUnion());
        self::assertSame('Something New', $fixture[0]->getTree());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Person();
        $fixture->setFirstname('Value');
        $fixture->setLastname('Value');
        $fixture->setBirth('Value');
        $fixture->setDeath('Value');
        $fixture->setBirthDaySure('Value');
        $fixture->setBirthMonthSure('Value');
        $fixture->setBirthYearSure('Value');
        $fixture->setDeathDaySure('Value');
        $fixture->setDeathMonthSure('Value');
        $fixture->setDeathYearSure('Value');
        $fixture->setPortrait('Value');
        $fixture->setBio('Value');
        $fixture->setUnions('Value');
        $fixture->setParentUnion('Value');
        $fixture->setTree('Value');

        $this->manager->remove($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/person/');
        self::assertSame(0, $this->repository->count([]));
    }
}
