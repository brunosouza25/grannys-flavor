<?php

namespace App\Repository;

use App\Entity\Vouchers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vouchers>
 *
 * @method Vouchers|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vouchers|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vouchers[]    findAll()
 * @method Vouchers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VouchersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vouchers::class);
    }

    public function add(Vouchers $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Vouchers $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getVouchers()
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $vouchers = $queryBuilder
            ->select('v')
            ->from('App\Entity\Vouchers', 'v')
            ->getQuery()
            ->getArrayResult();


        return $vouchers;
    }

    public function getVoucher($id)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $voucher = $queryBuilder
            ->select('v')
            ->from('App\Entity\Vouchers', 'v')
            ->where("v.id = $id")
            ->getQuery()
            ->getArrayResult();


        return $voucher[0];
    }
    public function newVoucher($voucherName, $voucherPercentage, $voucherActive, $expirationDate)
    {
        $voucher = new Vouchers();
        $voucher->setName($voucherName);
        $voucher->setPercentage($voucherPercentage);
        $voucher->setActive($voucherActive);
        $voucher->setExpirationDate($expirationDate);

        $this->getEntityManager()->persist($voucher);
        $this->getEntityManager()->flush();
    }

    public function changeVoucherState($voucherId)
    {
        $voucher = $this->getVoucher($voucherId);
        $queryBuilder = $this->createQueryBuilder('v');

        $query = $queryBuilder
            ->update('App\Entity\Vouchers', 'v')
            ->set('v.active', ':active')
            ->where('v.id = :id')
            ->setParameter('active', !$voucher['active'])
            ->setParameter('id', $voucherId)
            ->getQuery();

        $query->execute();
    }

    public function checkVoucher($voucher)
    {
        $en = $this->getEntityManager();
        $queryBuilder = $en->createQueryBuilder();

        $query = $queryBuilder
            ->select('v')
            ->from('App\Entity\Vouchers', 'v')
            ->where("v.name = '$voucher'")
            ->getQuery();
            $vouchers = $query->getArrayResult();

        return $vouchers;
    }
    public function checkVoucherById($voucherId)
    {
        $en = $this->getEntityManager();
        $queryBuilder = $en->createQueryBuilder();

        $query = $queryBuilder
            ->select('v')
            ->from('App\Entity\Vouchers', 'v')
            ->where("v.id = $voucherId")
            ->getQuery();
            $vouchers = $query->getArrayResult();

        return $vouchers;
    }

//    /**
//     * @return Vouchers[] Returns an array of Vouchers objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Vouchers
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
