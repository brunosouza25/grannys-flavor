<?php

namespace App\Repository;

use App\Entity\Foodadicionalconnectionitemmultiple;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Foodadicionalconnectionitemmultiple>
 *
 * @method Foodadicionalconnectionitemmultiple|null find($id, $lockMode = null, $lockVersion = null)
 * @method Foodadicionalconnectionitemmultiple|null findOneBy(array $criteria, array $orderBy = null)
 * @method Foodadicionalconnectionitemmultiple[]    findAll()
 * @method Foodadicionalconnectionitemmultiple[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FoodadicionalconnectionitemmultipleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Foodadicionalconnectionitemmultiple::class);
    }

    public function add(Foodadicionalconnectionitemmultiple $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Foodadicionalconnectionitemmultiple $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Foodadicionalconnectionitemmultiple[] Returns an array of Foodadicionalconnectionitemmultiple objects
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

//    public function findOneBySomeField($value): ?Foodadicionalconnectionitemmultiple
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
