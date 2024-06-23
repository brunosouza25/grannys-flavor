<?php

namespace App\Repository;

use App\Entity\PayByrdConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PayByrdConfig>
 *
 * @method PayByrdConfig|null find($id, $lockMode = null, $lockVersion = null)
 * @method PayByrdConfig|null findOneBy(array $criteria, array $orderBy = null)
 * @method PayByrdConfig[]    findAll()
 * @method PayByrdConfig[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PayByrdConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PayByrdConfig::class);
    }

    public function add(PayByrdConfig $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PayByrdConfig $entity, bool $flush = false): void
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
//     * @return PayByrdConfig[] Returns an array of PayByrdConfig objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?PayByrdConfig
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
