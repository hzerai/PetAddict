<?php

namespace App\Repository;

use App\Entity\Address;
use App\Entity\Adoption;
use App\Entity\Animal;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\AST\Join;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Adoption|null find($id, $lockMode = null, $lockVersion = null)
 * @method Adoption|null findOneBy(array $criteria, array $orderBy = null)
 * @method Adoption[]    findAll()
 * @method Adoption[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Adoption[]    findPaged($page, $size)
 */
class AdoptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Adoption::class);
    }

    /**
     * @return Adoption[] Returns an array of Adoption objects
     */
    public function findWithCriteria($criteria = null,  array $orderBy = null, $limit = null, $offset = null)
    {
        $query =  $this->createQueryBuilder('a');
        if (isset($criteria['urgent'])) {
            $query->andWhere('a.urgent = :urgent')
                ->setParameter('urgent', $criteria['urgent']);
        }
        if (isset($criteria['status'])) {
            $query->andWhere('a.status = :status')
                ->setParameter('status', $criteria['status']);
        } else {
            $query->andWhere('a.status = :status')
                ->setParameter('status', 'CREATED');
        }
        if (count($criteria) > 0) {
            if (isset($criteria['espece']) || isset($criteria['age']) || isset($criteria['couleur']) || isset($criteria['type']) || isset($criteria['taille']) || isset($criteria['sexe'])) {
                $query->innerJoin(
                    Animal::class,
                    'b',
                    'WITH',
                    'b.id = a.animal'
                );
                if (isset($criteria['espece'])) {
                    $query->andWhere('b.espece = :espece')
                        ->setParameter('espece', $criteria['espece']);
                }
                if (isset($criteria['couleur'])) {
                    $query->andWhere('b.couleur = :couleur')
                        ->setParameter('couleur', $criteria['couleur']);
                }
                if (isset($criteria['age'])) {
                    $query->andWhere('b.age = :age')
                        ->setParameter('age', $criteria['age']);
                }
                if (isset($criteria['type'])) {
                    $query->andWhere('b.type = :type')
                        ->setParameter('type', $criteria['type']);
                }
                if (isset($criteria['taille'])) {
                    $query->andWhere('b.taille = :taille')
                        ->setParameter('taille', $criteria['taille']);
                }
                if (isset($criteria['sexe'])) {
                    $query->andWhere('b.sexe = :sexe')
                        ->setParameter('sexe', $criteria['sexe']);
                }
            }
            if (isset($criteria['ville']) || isset($criteria['municipality'])) {
                $query->innerJoin(
                    User::class,
                    'c',
                    'WITH',
                    'c.id = a.userId'
                );
                $query->innerJoin(
                    Address::class,
                    'd',
                    'WITH',
                    'd.id = c.addressId'
                );
                if (isset($criteria['ville'])) {
                    $query->andWhere('d.ville = :ville')
                        ->setParameter('ville', $criteria['ville']);
                }
                if (isset($criteria['municipality'])) {
                    $query->andWhere('d.municipality = :municipality')
                        ->setParameter('municipality', $criteria['municipality']);
                }
            }
            $query->select('a');
            if (isset($criteria['user_id'])) {
                $query->andWhere('a.userId = :userId')
                    ->setParameter('userId', $criteria['user_id']);
            }
        }
        return  $query->orderBy('a.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Adoption[] Returns an array of Adoption objects
     */
    public function elasticSearch($key)
    {
        $query =  $this->createQueryBuilder('a');
      
        $query->orWhere('a.title LIKE :title')
            ->setParameter('title', '%' . $key . '%');
        $query->orWhere('a.description LIKE :description')
            ->setParameter('description', '%' . $key . '%');
        $query->innerJoin(
            Animal::class,
            'b',
            'WITH',
            'b.id = a.animal'
        );
        $query->orWhere('b.espece LIKE :espece')
            ->setParameter('espece', '%' . $key . '%');
        $query->orWhere('b.type LIKE :type')
            ->setParameter('type', '%' . $key . '%');
        $query->orWhere('b.taille LIKE :taille')
            ->setParameter('taille', '%' . $key . '%');
        $query->orWhere('b.sexe LIKE :sexe')
            ->setParameter('sexe', '%' . $key . '%');
        $query->orWhere('b.couleur LIKE :couleur')
            ->setParameter('couleur', '%' . $key . '%');
        $query->andWhere('a.status = :status')
            ->setParameter('status', 'CREATED');
        $query->select('a');
        $result1 =   $query->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();

        $query =  $this->createQueryBuilder('a');
        $query->innerJoin(
            Animal::class,
            'b',
            'WITH',
            'b.id = a.animal'
        );
        $query->innerJoin(
            User::class,
            'c',
            'WITH',
            'c.id = a.userId'
        );
        $query->innerJoin(
            Address::class,
            'd',
            'WITH',
            'd.id = c.addressId'
        );
        $query->orWhere('d.ville LIKE :ville')
            ->setParameter('ville', '%' . $key . '%');
        $query->orWhere('d.municipality LIKE :municipality')
            ->setParameter('municipality', '%' . $key . '%');
        $query->orWhere('d.details LIKE :details')
            ->setParameter('details', '%' . $key . '%');
        $query->andWhere('a.status = :status')
            ->setParameter('status', 'CREATED');
        $query->select('a');
        $result2 =   $query->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult();

        return array_merge($result1, $result2);
    }

    /**
     * @return Adoption[] Returns an array of Adoption objects
     */
    public function countFiltered($criteria = null)
    {
        $query =  $this->createQueryBuilder('a');
        if (isset($criteria['urgent'])) {
            $query->andWhere('a.urgent = :urgent')
                ->setParameter('urgent', $criteria['urgent']);
        }
        if (isset($criteria['status'])) {
            $query->andWhere('a.status = :status')
                ->setParameter('status', $criteria['status']);
        } else {
            $query->andWhere('a.status = :status')
                ->setParameter('status', 'CREATED');
        }
        if (count($criteria) > 0) {
            if (isset($criteria['espece']) || isset($criteria['age']) || isset($criteria['couleur']) || isset($criteria['type']) || isset($criteria['taille']) || isset($criteria['sexe'])) {
                $query->innerJoin(
                    Animal::class,
                    'b',
                    'WITH',
                    'b.id = a.animal'
                );
                if (isset($criteria['espece'])) {
                    $query->andWhere('b.espece = :espece')
                        ->setParameter('espece', $criteria['espece']);
                }
                if (isset($criteria['couleur'])) {
                    $query->andWhere('b.couleur = :couleur')
                        ->setParameter('couleur', $criteria['couleur']);
                }
                if (isset($criteria['age'])) {
                    if ($criteria['age'] == 'Senior') {
                        $query->andWhere('b.age >= :age')
                            ->setParameter('age', 4);
                    } else if ($criteria['age'] == 'Bébé') {
                        $query->andWhere('b.age = :age')
                            ->setParameter('age', 1);
                    } else if ($criteria['age'] == 'Junior') {
                        $query->andWhere('b.age = :age')
                            ->setParameter('age', 2);
                    } else if ($criteria['age'] == 'Adulte') {
                        $query->andWhere('b.age = :age')
                            ->setParameter('age', 3);
                    }
                }
                if (isset($criteria['type'])) {
                    $query->andWhere('b.type = :type')
                        ->setParameter('type', $criteria['type']);
                }
                if (isset($criteria['taille'])) {
                    $query->andWhere('b.taille = :taille')
                        ->setParameter('taille', $criteria['taille']);
                }
                if (isset($criteria['sexe'])) {
                    $query->andWhere('b.sexe = :sexe')
                        ->setParameter('sexe', $criteria['sexe']);
                }
            }
            if (isset($criteria['ville']) || isset($criteria['municipality'])) {
                $query->innerJoin(
                    User::class,
                    'c',
                    'WITH',
                    'c.id = a.userId'
                );
                $query->innerJoin(
                    Address::class,
                    'd',
                    'WITH',
                    'd.id = c.addressId'
                );
                if (isset($criteria['ville'])) {
                    $query->andWhere('d.ville = :ville')
                        ->setParameter('ville', $criteria['ville']);
                }
                if (isset($criteria['municipality'])) {
                    $query->andWhere('d.municipality = :municipality')
                        ->setParameter('municipality', $criteria['municipality']);
                }
            }
            $query->select('a');
            if (isset($criteria['user_id'])) {
                $query->andWhere('a.userId = :userId')
                    ->setParameter('userId', $criteria['user_id']);
            }
        }
        return  $query->select('count(a.id)')->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return Adoption[] Returns an array of Adoption objects
     */
    public function findPaged($offset, $size)
    {
        return $this->createQueryBuilder('a')
            ->where('a.status = :status')
            ->setParameter('status', 'CREATED')
            ->orderBy('a.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($size)
            ->getQuery()
            ->getResult();
    }


    public function coupdecoeur(): ?Adoption
    {
        $count = $this->count([]);
        $offset = random_int(0, $count - 1);
        return $this->createQueryBuilder('a')
            ->where('a.status = :status')
            ->setParameter('status', 'CREATED')
            ->getQuery()
            ->setFirstResult($offset)
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }


    /*
    public function findOneBySomeField($value): ?Adoption
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
