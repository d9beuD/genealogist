<?php

namespace App\Repository;

use App\Entity\Tree;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends ServiceEntityRepository<Tree>
 *
 * @method Tree|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tree|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tree[]    findAll()
 * @method Tree[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TreeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tree::class);
    }

    public function save(Tree $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Tree $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllPaginated(Request $request)
    {
        $queryBuilder = $this->createQueryBuilder('t');

        $limit = $request->get('limit');
        $offset = $request->get('offset') ?? 100;

        if ($limit) {
            $queryBuilder
                ->setMaxResults($limit)
                ->setFirstResult($offset);
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    public function findAllCount(): int
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
