<?php

namespace App\Repository;

use App\Entity\Foodadicionalcategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Foodadicionalcategory>
 *
 * @method Foodadicionalcategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method Foodadicionalcategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method Foodadicionalcategory[]    findAll()
 * @method Foodadicionalcategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FoodadicionalcategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Foodadicionalcategory::class);
    }

    public function add(Foodadicionalcategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Foodadicionalcategory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Foodadicionalcategory[] Returns an array of Foodadicionalcategory objects
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

//    public function findOneBySomeField($value): ?Foodadicionalcategory
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
