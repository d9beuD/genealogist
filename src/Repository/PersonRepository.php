<?php

namespace App\Repository;

use App\Entity\FavoriteMember;
use App\Entity\Person;
use App\Entity\Tree;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Person>
 *
 * @method Person|null find($id, $lockMode = null, $lockVersion = null)
 * @method Person|null findOneBy(array $criteria, array $orderBy = null)
 * @method Person[]    findAll()
 * @method Person[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    public function getOrphanMembers(Tree $tree, ?\DateTime $bornAfter = null): array
    {
        // Select members that are not part of any union
        // and that are born after parents birth date
        $queryBuilder = $this->createQueryBuilder('p')
            ->leftJoin('p.tree', 't')
            ->leftJoin('p.parentUnion', 'u')
            ->andWhere('t.id = :treeId')
            ->andWhere('p.parentUnion IS NULL')
            ->setParameter('treeId', $tree->getId())
        ;

        if ($bornAfter instanceof \DateTime) {
            // If a birth date is provided, select members that are born after it
            // or that have no birth date
            $queryBuilder
                ->andWhere('p.birth IS NULL OR p.birth > :bornAfter')
                ->setParameter('bornAfter', $bornAfter)
            ;
        }

        return $queryBuilder
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return array<int, Person>
     */
    public function findFavoritesInTree(Tree $tree, User $user): array
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $query = $queryBuilder
            ->select('f')
            ->from(FavoriteMember::class, 'f')
            ->leftJoin('f.person', 'p')
            ->where('f.user = :user')
            ->andWhere('p.tree = :tree')
            ->setParameter('user', $user)
            ->setParameter('tree', $tree)
            ->getQuery()
        ;

        return array_map(static fn (FavoriteMember $favoriteMember): ?Person => $favoriteMember->getPerson(), $query->getResult());
    }
}
