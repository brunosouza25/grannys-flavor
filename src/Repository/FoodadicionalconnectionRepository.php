<?php

namespace App\Repository;

use App\Entity\Foodadicionalconnection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Foodadicionalconnection>
 *
 * @method Foodadicionalconnection|null find($id, $lockMode = null, $lockVersion = null)
 * @method Foodadicionalconnection|null findOneBy(array $criteria, array $orderBy = null)
 * @method Foodadicionalconnection[]    findAll()
 * @method Foodadicionalconnection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FoodadicionalconnectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Foodadicionalconnection::class);
    }

    public function add(Foodadicionalconnection $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Foodadicionalconnection $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Foodadicionalconnection[] Returns an array of Foodadicionalconnection objects
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

//    public function findOneBySomeField($value): ?Foodadicionalconnection
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
