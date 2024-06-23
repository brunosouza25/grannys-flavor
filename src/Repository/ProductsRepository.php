<?php

namespace App\Repository;

use App\Entity\Products;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @extends ServiceEntityRepository<Products>
 *
 * @method Products|null find($id, $lockMode = null, $lockVersion = null)
 * @method Products|null findOneBy(array $criteria, array $orderBy = null)
 * @method Products[]    findAll()
 * @method Products[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Products::class);
    }

    public function add(Products $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Products $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function getProductGridStatus($productGridId)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $productGrid = $queryBuilder
            ->select('pg')
            ->from('App\Entity\ProductsGrid', 'pg')
            ->where('pg.id = :id')
            ->setParameter('id', $productGridId)
            ->getQuery()
            ->getResult();


        return $productGrid[0];
    }

    public function changeProductGridStatus($status, $productGridId): void
    {
        $queryBuilder = $this->createQueryBuilder('pg');
        $query = $queryBuilder
            ->update('App\Entity\ProductsGrid', 'pg')
            ->set('pg.status', ':status')
            ->where('pg.id = :id')
            ->setParameter('status', $status)
            ->setParameter('id', $productGridId)
            ->getQuery();

        $query->execute();

    }

//    /**
//     * @return Products[] Returns an array of Products objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Products
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function getProductsId($producId = null)
    {
        $conn = $this->getEntityManager()->getConnection();

        $query = "select group_concat(code) as ids from products where state = 1 and deleted = 0 ";
        if (!is_null($producId)) {
            $query.= "and code in ($producId)";
        }

        $productIds = $conn->query($query)->fetch();
        return $productIds['ids'];
    }

    public function getProductsGridStock($colorId, $sizeId, $productId)
    {
        $conn = $this->getEntityManager()->getConnection();

        $columns = "SELECT pg.stock AS stock, pg.code AS code FROM products_grid AS pg ";
        $where = "WHERE pg.product_id = $productId";

        if (!is_null($colorId)) {
            $where .= " AND pg.grid_color_id = $colorId";
        }

        if (!is_null($sizeId)) {
            $where .= " AND pg.grid_size_id = $sizeId";
        }

        $query = $columns . $where;
        $result = new \stdClass();
        $result = $conn->query($query)->fetch();

        if ($result) {

            //dd($result);
            return $result;

        }
        return null;
    }

    public function getProductsGridId($producId)
    {
        $conn = $this->getEntityManager()->getConnection();

        $query = "select group_concat(pg.code) as ids from products_grid as pg where pg.status = 1 AND pg.code IS NOT null AND pg.code != '' ";

        if (!$producId == 0) {
            $query. "and pg.product_id between = [$producId]";
        }

        $productGridIds = $conn->query($query)->fetch();
        return $productGridIds['ids'];
    }
//    test
    public function syncStock($productId, $stock){
        $conn = $this->getEntityManager()->getConnection();

        $date = date('Y-m-d H:i:s');

        $query = "update products set stock = $stock, update_status = 1, last_update = '$date' where code = $productId";

        $conn->query($query);
    }
    public function syncStockGrid($productId, $stock){
        $conn = $this->getEntityManager()->getConnection();

        $date = date('Y-m-d H:i:s');

        $query = "update products_grid set stock = $stock, update_status = 1, last_update = '$date' where code = $productId";

        $conn->query($query);
    }
    public function updateStockStatus($status, $grid, $productId = null){
        $conn = $this->getEntityManager()->getConnection();

        $table = 'products';

        if($grid) {
            $table = 'products_grid';
        }

        $query = "update $table set update_status = $status";

        $where = '';

        if (!is_null($productId)) {

            $where = " where code = $productId";
        }
        $conn->query($query . $where);

    }
}
