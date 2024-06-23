<?php

namespace App\Repository;

use App\Entity\Guestcontactaddress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Guestcontactaddress>
 *
 * @method Guestcontactaddress|null find($id, $lockMode = null, $lockVersion = null)
 * @method Guestcontactaddress|null findOneBy(array $criteria, array $orderBy = null)
 * @method Guestcontactaddress[]    findAll()
 * @method Guestcontactaddress[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GuestcontactaddressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Guestcontactaddress::class);
    }

    public function add(Guestcontactaddress $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Guestcontactaddress $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Guestcontactaddress[] Returns an array of Guestcontactaddress objects
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

//    public function findOneBySomeField($value): ?Guestcontactaddress
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
