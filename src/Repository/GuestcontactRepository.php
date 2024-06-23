<?php

namespace App\Repository;

use App\Entity\Guestcontact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Guestcontact>
 *
 * @method Guestcontact|null find($id, $lockMode = null, $lockVersion = null)
 * @method Guestcontact|null findOneBy(array $criteria, array $orderBy = null)
 * @method Guestcontact[]    findAll()
 * @method Guestcontact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GuestcontactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Guestcontact::class);
    }

    public function add(Guestcontact $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Guestcontact $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//
//     * @return Guestcontact[] Returns an array of Guestcontact objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('g.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

    public function findOneBySomeField($value): ?Guestcontact
   {
        return $this->createQueryBuilder('gc')
            ->andWhere('gc.session = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
   }

    public function getUserByOrderId($orderId)
   {
       $en = $this->getEntityManager();

       $qb2 = $en->createQueryBuilder();

       $query = $qb2->select('g')
           ->from('App\Entity\Orders', 'o')
           ->innerJoin('App\Entity\Guestcontact', 'g', 'WITH', 'o.user_id = g.id')
           ->where("o.id = :orderId")
           ->setParameter('orderId', $orderId)
           ->getQuery();


       $user = $query->getArrayResult()[0];

       return $user;
   }
}
