<?php

namespace App\Repository;

use App\Entity\OrderPayments;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrderPayments>
 *
 * @method OrderPayments|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderPayments|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderPayments[]    findAll()
 * @method OrderPayments[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderPaymentsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderPayments::class);
    }

    public function add(OrderPayments $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(OrderPayments $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function changePaymentStatus($orderPaymentId, $status, $paymentInfo = null): void
    {
        $queryBuilder = $this->createQueryBuilder('op');

        if (!is_null($paymentInfo)) {
            $query = $queryBuilder
                ->update(OrderPayments::class, 'op')
                ->set('op.status', ':status')
                ->set('op.payment_date', ':payment_date')
                ->where('op.id = :id')
                ->setParameter('status', $status)
                ->setParameter('id', $orderPaymentId)
                ->setParameter('payment_date', $paymentInfo->payment_datetime)
                ->getQuery();
        } else {
            $query = $queryBuilder
                ->update('App\Entity\OrderPayments', 'op')
                ->set('op.status', ':status')
                ->where('op.id = :id')
                ->setParameter('status', $status)
                ->setParameter('id', $orderPaymentId)
                ->getQuery();
        }



        $query->execute();

    }

//    /**
//     * @return OrderPayments[] Returns an array of OrderPayments objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?OrderPayments
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
