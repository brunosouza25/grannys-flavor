<?php

namespace App\Repository;

use App\Entity\Zonemapdrawing;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Zonemapdrawing>
 *
 * @method Zonemapdrawing|null find($id, $lockMode = null, $lockVersion = null)
 * @method Zonemapdrawing|null findOneBy(array $criteria, array $orderBy = null)
 * @method Zonemapdrawing[]    findAll()
 * @method Zonemapdrawing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZonemapdrawingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Zonemapdrawing::class);
    }

    public function add(Zonemapdrawing $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Zonemapdrawing $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Zonemapdrawing[] Returns an array of Zonemapdrawing objects
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

//    public function findOneBySomeField($value): ?Zonemapdrawing
//    {
//        return $this->createQueryBuilder('z')
//            ->andWhere('z.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
