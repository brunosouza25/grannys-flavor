<?php

namespace App\Repository;

use App\Entity\Orderlist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Orderlist>
 *
 * @method Orderlist|null find($id, $lockMode = null, $lockVersion = null)
 * @method Orderlist|null findOneBy(array $criteria, array $orderBy = null)
 * @method Orderlist[]    findAll()
 * @method Orderlist[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderlistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Orderlist::class);
    }

    public function add(Orderlist $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Orderlist $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getOrderProducts($orderId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $query = $qb
            ->select('o')
            ->from('App\Entity\OrderList', 'o')
            ->where('o.orderid = :orderId')
            ->setParameter('orderId', $orderId)
            ->getQuery();

        $result = $query->getResult();

        return $result;
    }

//    /**
//     * @return Orderlist[] Returns an array of Orderlist objects
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

//    public function findOneBySomeField($value): ?Orderlist
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
