<?php

namespace App\Repository;

use App\Entity\Foodadicionalconnectionintem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Foodadicionalconnectionintem>
 *
 * @method Foodadicionalconnectionintem|null find($id, $lockMode = null, $lockVersion = null)
 * @method Foodadicionalconnectionintem|null findOneBy(array $criteria, array $orderBy = null)
 * @method Foodadicionalconnectionintem[]    findAll()
 * @method Foodadicionalconnectionintem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FoodadicionalconnectionintemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Foodadicionalconnectionintem::class);
    }

    public function add(Foodadicionalconnectionintem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Foodadicionalconnectionintem $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Foodadicionalconnectionintem[] Returns an array of Foodadicionalconnectionintem objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Foodadicionalconnectionintem
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
