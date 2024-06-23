<?php

namespace App\Repository;

use App\Entity\Zonesoftdata;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Zonesoftdata>
 *
 * @method Zonesoftdata|null find($id, $lockMode = null, $lockVersion = null)
 * @method Zonesoftdata|null findOneBy(array $criteria, array $orderBy = null)
 * @method Zonesoftdata[]    findAll()
 * @method Zonesoftdata[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZonesoftdataRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Zonesoftdata::class);
    }

    public function add(Zonesoftdata $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Zonesoftdata $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Zonesoftdata[] Returns an array of Zonesoftdata objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('z')
//            ->andWhere('z.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('z.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Zonesoftdata
//    {
//        return $this->createQueryBuilder('z')
//            ->andWhere('z.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
