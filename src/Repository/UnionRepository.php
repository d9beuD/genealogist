<?php

namespace App\Repository;

use App\Entity\Union;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Union>
 *
 * @method Union|null find($id, $lockMode = null, $lockVersion = null)
 * @method Union|null findOneBy(array $criteria, array $orderBy = null)
 * @method Union[]    findAll()
 * @method Union[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UnionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Union::class);
    }

    //    /**
    //     * @return Union[] Returns an array of Union objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Union
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
