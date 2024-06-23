<?php

namespace App\Repository;

use App\Entity\MultibancoPayment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MultibancoPayment>
 *
 * @method MultibancoPayment|null find($id, $lockMode = null, $lockVersion = null)
 * @method MultibancoPayment|null findOneBy(array $criteria, array $orderBy = null)
 * @method MultibancoPayment[]    findAll()
 * @method MultibancoPayment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MultibancoPaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MultibancoPayment::class);
    }

    public function add(MultibancoPayment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MultibancoPayment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function changeMultibancoPaymentStatus($sourceId, $status)
    {
        $queryBuilder = $this->createQueryBuilder('mp');
        $query = $queryBuilder
            ->update('App\Entity\MultibancoPayment', 'mp')
            ->set('mp.status', ':status')
            ->where('mp.source = :source')
            ->setParameter('status', $status)
            ->setParameter('source', $sourceId)
            ->getQuery();

        $query->execute();
    }

//    /**
//     * @return MultibancoPayment[] Returns an array of MultibancoPayment objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?MultibancoPayment
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
