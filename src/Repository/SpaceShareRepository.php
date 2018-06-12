<?php

namespace App\Repository;

use App\Entity\SpaceShare;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SpaceShare|null find($id, $lockMode = null, $lockVersion = null)
 * @method SpaceShare|null findOneBy(array $criteria, array $orderBy = null)
 * @method SpaceShare[]    findAll()
 * @method SpaceShare[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SpaceShareRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SpaceShare::class);
    }

//    /**
//     * @return SpaceShare[] Returns an array of SpaceShare objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SpaceShare
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
