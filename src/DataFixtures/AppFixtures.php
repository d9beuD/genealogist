<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Person;
use App\Entity\Source;
use App\Entity\User;
use App\Factory\FavoriteMemberFactory;
use App\Factory\PersonFactory;
use App\Factory\SourceFactory;
use App\Factory\TreeFactory;
use App\Factory\UnionFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        protected UserPasswordHasherInterface $userPasswordHasher,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $password = $this->userPasswordHasher->hashPassword(new User(), 'password');

        $user = UserFactory::new()->verified()->create([
            'email' => 'john.doe@example.com',
            'password' => $password,
            'firstname' => 'John',
            'lastname' => 'Doe',
        ]);

        $tree = TreeFactory::createOne([
            'user' => $user,
            'name' => 'Riverton Roots',
            'createdAt' => new \DateTimeImmutable('2024-01-15 09:00:00'),
        ]);

        // Generation 1 — grandparents
        $arthurRiverton = PersonFactory::new()->male()->deceased()->unsureBirth()->create([
            'tree' => $tree,
            'firstname' => 'Arthur',
            'lastname' => 'Riverton',
            'birth' => new \DateTimeImmutable('1932-04-12'),
            'birthPlace' => 'Rouen, France',
            'death' => new \DateTimeImmutable('2012-09-18'),
            'deathPlace' => 'Lyon, France',
            'bio' => 'Arthur kept letters, maps, and train tickets from every family journey.',
        ]);

        $eliseMartin = PersonFactory::new()->female()->deceased()->create([
            'tree' => $tree,
            'firstname' => 'Élise',
            'lastname' => 'Martin',
            'birth' => new \DateTimeImmutable('1936-11-03'),
            'birthPlace' => 'Tours, France',
            'death' => new \DateTimeImmutable('2018-05-22'),
            'deathPlace' => 'Lyon, France',
            'bio' => 'Élise taught music and wrote careful notes in the family albums.',
        ]);

        $hugoLemoine = PersonFactory::new()->male()->deceased()->create([
            'tree' => $tree,
            'firstname' => 'Hugo',
            'lastname' => 'Lemoine',
            'birth' => new \DateTimeImmutable('1934-02-27'),
            'birthPlace' => 'Nantes, France',
            'death' => new \DateTimeImmutable('2009-12-14'),
            'deathPlace' => 'Bordeaux, France',
            'bio' => 'Hugo restored boats and passed the craft to his children.',
        ]);

        $claireBenoit = PersonFactory::new()->female()->living()->create([
            'tree' => $tree,
            'firstname' => 'Claire',
            'lastname' => 'Benoit',
            'birth' => new \DateTimeImmutable('1938-07-19'),
            'birthPlace' => 'Brest, France',
            'bio' => 'Claire preserves recipes, postcards, and stories from the coast.',
        ]);

        // Generation 2 — parents
        $marcRiverton = PersonFactory::new()->male()->living()->create([
            'tree' => $tree,
            'firstname' => 'Marc',
            'lastname' => 'Riverton',
            'birth' => new \DateTimeImmutable('1963-06-08'),
            'birthPlace' => 'Lyon, France',
            'bio' => 'Marc digitized the family photographs during winter evenings.',
        ]);

        $anneLemoine = PersonFactory::new()->female()->living()->create([
            'tree' => $tree,
            'firstname' => 'Anne',
            'lastname' => 'Lemoine',
            'birth' => new \DateTimeImmutable('1965-03-21'),
            'birthPlace' => 'Bordeaux, France',
            'bio' => 'Anne records oral histories and validates dates with civil records.',
        ]);

        // Generation 3 — children
        $lucasRiverton = PersonFactory::new()->male()->living()->create([
            'tree' => $tree,
            'firstname' => 'Lucas',
            'lastname' => 'Riverton',
            'birth' => new \DateTimeImmutable('1991-10-05'),
            'birthPlace' => 'Paris, France',
            'bio' => "Lucas started the current tree after finding Arthur's notebook.",
        ]);

        $emmaRiverton = PersonFactory::new()->female()->living()->create([
            'tree' => $tree,
            'firstname' => 'Emma',
            'lastname' => 'Riverton',
            'birth' => new \DateTimeImmutable('1994-12-28'),
            'birthPlace' => 'Paris, France',
            'bio' => 'Emma tags portraits and translates family letters.',
        ]);

        // Additional people
        $sofiaMoreau = PersonFactory::new()->female()->living()->create([
            'tree' => $tree,
            'firstname' => 'Sofia',
            'lastname' => 'Moreau',
            'birth' => new \DateTimeImmutable('1992-05-16'),
            'birthPlace' => 'Versailles, France',
            'bio' => 'Sofia connects the Riverton branch with contemporary records and interviews.',
        ]);

        $milaRiverton = PersonFactory::new()->female()->living()->create([
            'tree' => $tree,
            'firstname' => 'Mila',
            'lastname' => 'Riverton',
            'birth' => new \DateTimeImmutable('2020-03-09'),
            'birthPlace' => 'Paris, France',
            'bio' => 'Mila represents the newest documented generation of the tree.',
        ]);

        $noahRiverton = PersonFactory::new()->male()->living()->create([
            'tree' => $tree,
            'firstname' => 'Noah',
            'lastname' => 'Riverton',
            'birth' => new \DateTimeImmutable('2023-11-21'),
            'birthPlace' => 'Paris, France',
            'bio' => 'Noah completes the fourth generation in the demo family.',
        ]);

        $nicolasGauthier = PersonFactory::new()->male()->living()->create([
            'tree' => $tree,
            'firstname' => 'Nicolas',
            'lastname' => 'Gauthier',
            'birth' => new \DateTimeImmutable('1990-02-11'),
            'birthPlace' => 'Paris, France',
            'bio' => "Nicolas appears in the tree through Emma's former relationship.",
        ]);

        // Unions
        UnionFactory::new()->withFamily([$arthurRiverton, $eliseMartin], [$marcRiverton])->create([
            'married' => true,
            'startsAt' => new \DateTimeImmutable('1958-06-14'),
            'place' => 'Rouen, France',
            'description' => 'Civil marriage followed by a family lunch near the station.',
        ]);

        UnionFactory::new()->withFamily([$hugoLemoine, $claireBenoit], [$anneLemoine])->create([
            'married' => true,
            'startsAt' => new \DateTimeImmutable('1960-08-20'),
            'place' => 'Nantes, France',
            'description' => "Summer wedding recorded in Hugo's harbor journal.",
        ]);

        UnionFactory::new()->withFamily([$marcRiverton, $anneLemoine], [$lucasRiverton, $emmaRiverton])->create([
            'married' => true,
            'startsAt' => new \DateTimeImmutable('1988-09-17'),
            'place' => 'Lyon, France',
            'description' => 'Small ceremony with both families bringing photo albums.',
        ]);

        UnionFactory::new()->withFamily([$lucasRiverton, $sofiaMoreau], [$milaRiverton, $noahRiverton])->create([
            'married' => true,
            'startsAt' => new \DateTimeImmutable('2018-07-14'),
            'endsAt' => null,
            'place' => 'Paris, France',
            'description' => 'Public garden ceremony followed by interviews with both grandparents.',
        ]);

        UnionFactory::new()->withFamily([$emmaRiverton, $nicolasGauthier])->create([
            'married' => false,
            'startsAt' => new \DateTimeImmutable('2014-09-01'),
            'dayUnsure' => true,
            'endsAt' => new \DateTimeImmutable('2019-06-01'),
            'endDayUnsure' => true,
            'place' => 'Paris, France',
            'description' => 'Former partnership documented from shared residence records.',
        ]);

        // Source on Arthur
        SourceFactory::createOne([
            'person' => $arthurRiverton,
            'type' => Source::CERT_BIRTH,
            'url' => 'https://archives.example.test/riverton/arthur-birth',
            'comment' => 'Birth register transcription used for the demo tree.',
            'directProof' => true,
        ]);

        SourceFactory::createOne([
            'person' => $eliseMartin,
            'type' => Source::CERT_BAPTISM,
            'url' => 'https://archives.example.test/riverton/elise-baptism',
            'comment' => 'Baptism note cross-checks Élise\'s given names.',
            'directProof' => false,
        ]);

        SourceFactory::createOne([
            'person' => $marcRiverton,
            'type' => Source::CERT_MARRIAGE,
            'url' => 'https://archives.example.test/riverton/marc-anne-marriage',
            'comment' => 'Marriage certificate links both parent branches.',
            'directProof' => true,
        ]);

        SourceFactory::createOne([
            'person' => $hugoLemoine,
            'type' => Source::CERT_DEATH,
            'url' => 'https://archives.example.test/riverton/hugo-death',
            'comment' => 'Death certificate confirms final residence in Bordeaux.',
            'directProof' => true,
        ]);

        SourceFactory::createOne([
            'person' => $lucasRiverton,
            'type' => Source::CERT_MILITARY,
            'url' => 'https://archives.example.test/riverton/lucas-service',
            'comment' => 'Service record used as an indirect proof of residence.',
            'directProof' => false,
        ]);

        SourceFactory::createOne([
            'person' => $sofiaMoreau,
            'type' => Source::CERT_OTHER,
            'url' => 'https://archives.example.test/riverton/sofia-interview',
            'comment' => 'Interview transcript records living family context.',
            'directProof' => false,
        ]);

        // Favorite: Lucas marked by tree owner
        FavoriteMemberFactory::createOne([
            'user' => $user,
            'person' => $lucasRiverton,
        ]);

        FavoriteMemberFactory::createOne([
            'user' => $user,
            'person' => $emmaRiverton,
        ]);
    }
}
