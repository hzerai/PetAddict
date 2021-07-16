<?php

namespace App\Repository;

use App\Entity\Found;
use App\Entity\Animal;
use App\Entity\User;
use Doctrine\ORM\Query\AST\Join;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Found|null find($id, $lockMode = null, $lockVersion = null)
 * @method Found|null findOneBy(array $criteria, array $orderBy = null)
 * @method Found[]    findAll()
 * @method Found[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FoundRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Found::class);
    }

  

    /*
    public function findOneBySomeField($value): ?Found
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @return Found[] Returns an array of Found objects
     */
    public function findPaged($offset, $size, $status)
    {

        $query = $this->createQueryBuilder('a');
        if ($status != null) {
            $query->where('a.status = :status')
                ->setParameter('status', $status);
        }
        else 
        {
            $query->where('a.status = :status')
            ->setParameter('status', 'CREATED');
        }
        return $query->orderBy('a.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($size)
            ->getQuery()
            ->getResult();
    }
    public function count($status){
        $query = $this->createQueryBuilder('a');
        if ($status != null) {
            $query->where('a.status = :status')
                ->setParameter('status', $status);
        }
        else 
        {
            $query->where('a.status = :status')
            ->setParameter('status', 'CREATED');
        }
        return $query->select('count (a.id)') 
            ->getQuery()
            ->getSingleScalarResult();

    }
}