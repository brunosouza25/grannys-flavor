<?php

namespace App\Repository;

use App\Entity\Foodadicionalitems;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Foodadicionalitems>
 *
 * @method Foodadicionalitems|null find($id, $lockMode = null, $lockVersion = null)
 * @method Foodadicionalitems|null findOneBy(array $criteria, array $orderBy = null)
 * @method Foodadicionalitems[]    findAll()
 * @method Foodadicionalitems[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FoodadicionalitemsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Foodadicionalitems::class);
    }

    public function add(Foodadicionalitems $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Foodadicionalitems $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Foodadicionalitems[] Returns an array of Foodadicionalitems objects
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

//    public function findOneBySomeField($value): ?Foodadicionalitems
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
