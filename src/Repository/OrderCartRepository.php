<?php

namespace App\Repository;

use App\Entity\OrderCart;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrderCart>
 *
 * @method OrderCart|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderCart|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderCart[]    findAll()
 * @method OrderCart[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderCartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderCart::class);
    }

    public function add(OrderCart $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(OrderCart $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function checkSessionOrderCart($session)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $orderCart = $queryBuilder
            ->select('oc')
            ->from('App\Entity\OrderCart', 'oc')
            ->where("oc.session = '$session'")
            ->getQuery()
            ->getArrayResult();


        return $orderCart;
    }

    public function createOrderCart($session)
    {
        $orderCart = new OrderCart();
        $orderCart->setSession($session);


        $this->getEntityManager()->persist($orderCart);
        $this->getEntityManager()->flush();
    }


    public function linkVoucherToCart($orderCartId, $voucherId)
    {
        $queryBuilder = $this->createQueryBuilder('oc');
        $query = $queryBuilder
            ->update('App\Entity\OrderCart', 'oc')
            ->set('oc.voucher_id', ':voucher_id')
            ->where('oc.id = :id')
            ->setParameter('voucher_id', $voucherId)
            ->setParameter('id', $orderCartId)
            ->getQuery();

        $query->execute();

    }
    public function updateSession($oldSession, $newSession)
    {
        $queryBuilder = $this->createQueryBuilder('oc');
        $query = $queryBuilder
            ->update('App\Entity\OrderCart', 'oc')
            ->set('oc.session', ':newSession')
            ->where('oc.session = :oldSession')
            ->setParameter('newSession', $newSession)
            ->setParameter('oldSession', $oldSession)
            ->getQuery();

        $query->execute();

    }
    public function deleteOrderCart($orderCartId)
    {
        $queryBuilder = $this->createQueryBuilder('oc');
        $queryBuilder
            ->delete('App\Entity\OrderCart', 'oc')
            ->where('oc.id = :id')
            ->setParameter('id', $orderCartId);

        $query = $queryBuilder->getQuery();
        $query->execute();

    }

//    /**
//     * @return OrderCart[] Returns an array of OrderCart objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?OrderCart
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
