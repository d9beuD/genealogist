<?php

namespace App\Repository;

use App\Entity\Person;
use App\Entity\Tree;
use DateTime;
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

    public function getOrphanMembers(Tree $tree, ?DateTime $bornAfter = null): array
    {
        // Select members that are not part of any union
        // and that are born after parents birth date
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.tree', 't')
            ->leftJoin('p.parentUnion', 'u')
            ->andWhere('t.id = :treeId')
            ->andWhere('p.parentUnion IS NULL')
            ->setParameter('treeId', $tree->getId())
        ;

        if ($bornAfter) {
            // If a birth date is provided, select members that are born after it
            // or that have no birth date
            $qb
                ->andWhere('p.birth IS NULL OR p.birth > :bornAfter')
                ->setParameter('bornAfter', $bornAfter)
            ;
        }

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    //    /**
    //     * @return Person[] Returns an array of Person objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Person
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
