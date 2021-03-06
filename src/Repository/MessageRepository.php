<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * @return Message[] Returns an array of Message objects
     */
    public function findByUser($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.fromUser = :from')
            ->setParameter('from', $value)
            ->orWhere('m.toUser = :to')
            ->setParameter('to', $value)
            ->orderBy('m.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Message[] Returns an array of Message objects
     */
    public function findUserNewMessages($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.vu = :vu')
            ->setParameter('vu', false)
            ->andWhere('m.toUser = :to')
            ->setParameter('to', $value)
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }


    /*
    public function findOneBySomeField($value): ?Message
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
