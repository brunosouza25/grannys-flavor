<?php

namespace App\Repository;

use App\Entity\StripeConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StripeConfig>
 *
 * @method StripeConfig|null find($id, $lockMode = null, $lockVersion = null)
 * @method StripeConfig|null findOneBy(array $criteria, array $orderBy = null)
 * @method StripeConfig[]    findAll()
 * @method StripeConfig[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StripeConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StripeConfig::class);
    }

    public function add(StripeConfig $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(StripeConfig $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getToken($token)
    {
        $en = $this->getEntityManager();

        $query = $en->createQueryBuilder();

        $query
            ->select("pbc.$token")
            ->from($this->getClassName(), 'pbc')
            ->where('pbc.id = 1');
        $token = $query->getQuery()->getSingleResult()[$token];
        return $token;
    }

//    /**
//     * @return StripeConfig[] Returns an array of StripeConfig objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?StripeConfig
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
