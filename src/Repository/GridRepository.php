<?php

namespace App\Repository;

use App\Entity\Grid;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Grid>
 *
 * @method Grid|null find($id, $lockMode = null, $lockVersion = null)
 * @method Grid|null findOneBy(array $criteria, array $orderBy = null)
 * @method Grid[]    findAll()
 * @method Grid[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GridRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Grid::class);
    }

    public function add(Grid $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Grid $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Grid[] Returns an array of Grid objects
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

//    public function findOneBySomeField($value): ?Grid
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function getColorsProduct($productId)
    {

        $conn = $this->getEntityManager()->getConnection();

        $productColors = $conn->query("SELECT g.image AS image 
                                                FROM products AS p 
                                        LEFT JOIN products_grid AS pg ON p.id = pg.product_id 
                                        LEFT JOIN grid AS g ON pg.grid_color_id = g.id
                                        WHERE p.state = 1 
                                            AND p.deleted = 0                                                                                                                      
                                            AND pg.product_id = $productId
                                            AND g.image IS NOT NULL GROUP BY (g.id);
                                        ")->fetchAll();

        return $productColors;
    }

    public function productGridStockSum($productId)
    {
        $conn = $this->getEntityManager()->getConnection();

        $productGridStockSum = $conn->query("SELECT SUM(stock) as stock FROM `products_grid` WHERE product_id = $productId;")->fetch();

        return $productGridStockSum;
    }
}
