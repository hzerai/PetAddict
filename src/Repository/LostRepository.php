<?php

namespace App\Repository;

use App\Entity\Lost;
use App\Entity\User;
use App\Entity\Address;
use App\Entity\Animal;
use Doctrine\ORM\Query\AST\Join;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Lost|null find($id, $lockMode = null, $lockVersion = null)
 * @method Lost|null findOneBy(array $criteria, array $orderBy = null)
 * @method Lost[]    findAll()
 * @method Lost[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Lost::class);
    }


    /*
    public function findOneBySomeField($value): ?Lost
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @return Lost[] Returns an array of Lost objects
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
