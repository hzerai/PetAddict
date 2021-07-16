<?php

namespace App\Repository;

use App\Entity\Veto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Veto|null find($id, $lockMode = null, $lockVersion = null)
 * @method Veto|null findOneBy(array $criteria, array $orderBy = null)
 * @method Veto[]    findAll()
 * @method Veto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VetoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Veto::class);
    }

    // /**
    //  * @return Veto[] Returns an array of Veto objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
    public function findPaged($page, $size)
    {
        if ($page < 1) {
            $page = 1;
        }
        $offset = ($page - 1) * $size;
        return $this->createQueryBuilder('a')
            ->orderBy('a.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($size)
            ->getQuery()
            ->getResult();
    }
    /*
    public function findOneBySomeField($value): ?Veto
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    /**
     * @return Veto[] Returns an array of Adoption objects
     */
    public function elasticSearch($key)
    {

        $query =  $this->createQueryBuilder('a');
        $query->Where('a.docteur LIKE :docteur')
            ->setParameter('docteur', '%' . $key . '%');
        $query->orWhere('a.description LIKE :description')
            ->setParameter('description', '%' . $key . '%');
        $query->orWhere('a.adresse LIKE :adresse')
            ->setParameter('adresse', '%' . $key . '%');
        return $query->getQuery()
            ->getResult();
    }
}
