<?php

namespace App\Repository;

use App\Entity\Zonesoftapi;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Zonesoftapi>
 *
 * @method Zonesoftapi|null find($id, $lockMode = null, $lockVersion = null)
 * @method Zonesoftapi|null findOneBy(array $criteria, array $orderBy = null)
 * @method Zonesoftapi[]    findAll()
 * @method Zonesoftapi[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZonesoftapiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Zonesoftapi::class);
    }

    public function add(Zonesoftapi $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Zonesoftapi $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Zonesoftapi[] Returns an array of Zonesoftapi objects
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

//    public function findOneBySomeField($value): ?Zonesoftapi
//    {
//        return $this->createQueryBuilder('z')
//            ->andWhere('z.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
