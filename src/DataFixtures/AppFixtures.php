<?php

namespace App\DataFixtures;

use App\Entity\Person;
use App\Entity\Tree;
use App\Entity\Union;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        protected UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $user = new User();
        $password = $this->userPasswordHasher->hashPassword($user, 'password');
        $user
            ->setEmail('john.doe@example.com')
            ->setPassword($password)
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setIsVerified(true)
        ;

        $tree = new Tree();
        $tree
            ->setOwner($user)
            ->setName('Doe\'s Family')
            ->setCreatedAt(new DateTimeImmutable())
        ;

        $grandParent1 = new Person();
        $grandParent1
            ->setTree($tree)
            ->setFirstname($faker->firstNameMale())
            ->setLastname($faker->lastName())
            ->setBio($faker->paragraph())
            ->setGender(Person::MALE)
            
            ->setBirth(new DateTimeImmutable('1944-01-02'))
            ->setBirthPlace('New York, NY')
            
            ->setDead(true)
            ->setDeath(new DateTimeImmutable('2010-02-03'))
            ->setDeathPlace('New York, NY')
        ;

        $grandParent2 = new Person();
        $grandParent2
            ->setTree($tree)
            ->setFirstname($faker->firstNameFemale())
            ->setLastname($faker->lastName())
            ->setBio($faker->paragraph())
            ->setGender(Person::FEMALE)
            
            ->setBirth(new DateTimeImmutable('1945-02-03'))
            ->setBirthPlace('Washington, DC')
            
            ->setDead(true)
            ->setDeath(new DateTimeImmutable('2011-03-04'))
            ->setDeathPlace('New York, NY')
        ;

        $grandParent3 = new Person();
        $grandParent3
            ->setTree($tree)
            ->setFirstname($faker->firstNameMale())
            ->setLastname($faker->lastName())
            ->setBio($faker->paragraph())
            ->setGender(Person::MALE)
            
            ->setBirth(new DateTimeImmutable('1944-01-02'))
            ->setBirthPlace('New York, NY')
            
            ->setDead(true)
            ->setDeath(new DateTimeImmutable('2010-02-03'))
            ->setDeathPlace('New York, NY')
        ;

        $grandParent4 = new Person();
        $grandParent4
            ->setTree($tree)
            ->setFirstname($faker->firstNameFemale())
            ->setLastname($faker->lastName())
            ->setBio($faker->paragraph())
            ->setGender(Person::FEMALE)
            
            ->setBirth(new DateTimeImmutable('1945-02-03'))
            ->setBirthPlace('Washington, DC')
            
            ->setDead(true)
            ->setDeath(new DateTimeImmutable('2011-03-04'))
            ->setDeathPlace('New York, NY')
        ;

        $parent1 = new Person();
        $parent1
            ->setTree($tree)
            ->setFirstname($faker->firstNameMale())
            ->setLastname($grandParent1->getLastname())
            ->setBio($faker->paragraph())
            ->setGender(Person::MALE)
            
            ->setBirth(new DateTimeImmutable('1970-04-05'))
            ->setBirthPlace('New York, NY')
        ;

        $parent2 = new Person();
        $parent2
            ->setTree($tree)
            ->setFirstname($faker->firstNameFemale())
            ->setLastname($grandParent3->getLastname())
            ->setBio($faker->paragraph())
            ->setGender(Person::FEMALE)
            
            ->setBirth(new DateTimeImmutable('1971-06-07'))
            ->setBirthPlace('Washington, DC')
        ;

        $child1 = new Person();
        $child1
            ->setTree($tree)
            ->setFirstname($faker->firstNameMale())
            ->setLastname($parent1->getLastname())
            ->setBio($faker->paragraph())
            
            ->setBirth(new DateTimeImmutable('1970-04-05'))
            ->setBirthPlace('New York, NY')
        ;

        $child2 = new Person();
        $child2
            ->setTree($tree)
            ->setFirstname($faker->firstNameFemale())
            ->setLastname($parent1->getLastname())
            ->setBio($faker->paragraph())
            
            ->setBirth(new DateTimeImmutable('1971-06-07'))
            ->setBirthPlace('New York, NY')
        ;

        $union1 = new Union();
        $union1
            ->addPerson($grandParent1)
            ->addPerson($grandParent2)
            ->addChild($parent1)
        ;

        $union2 = new Union();
        $union2
            ->addPerson($grandParent3)
            ->addPerson($grandParent4)
            ->addChild($parent2)
        ;

        $union3 = new Union();
        $union3
            ->addPerson($parent1)
            ->addPerson($parent2)
            ->addChild($child1)
            ->addChild($child2)
        ;

        $manager->persist($user);
        $manager->persist($tree);
        $manager->persist($grandParent1);
        $manager->persist($grandParent2);
        $manager->persist($grandParent3);
        $manager->persist($grandParent4);
        $manager->persist($parent1);
        $manager->persist($parent2);
        $manager->persist($child1);
        $manager->persist($child2);
        $manager->persist($union1);
        $manager->persist($union2);
        $manager->persist($union3);

        $manager->flush();
    }
}
