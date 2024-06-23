<?php

namespace App\Repository;

use App\Entity\Foodmenuitemsmultiple;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Foodmenuitemsmultiple>
 *
 * @method Foodmenuitemsmultiple|null find($id, $lockMode = null, $lockVersion = null)
 * @method Foodmenuitemsmultiple|null findOneBy(array $criteria, array $orderBy = null)
 * @method Foodmenuitemsmultiple[]    findAll()
 * @method Foodmenuitemsmultiple[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FoodmenuitemsmultipleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Foodmenuitemsmultiple::class);
    }

    public function add(Foodmenuitemsmultiple $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Foodmenuitemsmultiple $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Foodmenuitemsmultiple[] Returns an array of Foodmenuitemsmultiple objects
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

//    public function findOneBySomeField($value): ?Foodmenuitemsmultiple
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
