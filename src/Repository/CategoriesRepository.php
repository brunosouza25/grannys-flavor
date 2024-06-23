<?php

namespace App\Repository;

use App\Entity\Categories;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Categories>
 *
 * @method Categories|null find($id, $lockMode = null, $lockVersion = null)
 * @method Categories|null findOneBy(array $criteria, array $orderBy = null)
 * @method Categories[]    findAll()
 * @method Categories[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoriesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categories::class);
    }

    public function add(Categories $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Categories $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function getChildrenCategories($parentId)
    {
        $conn = $this->getEntityManager()->getConnection();

        $childrenCategories = $conn->query("SELECT * FROM categories WHERE parent_id = $parentId")->fetchAll();

        return $childrenCategories;
    }

    public function getChildCategory($categoryId)
    {

        $conn = $this->getEntityManager()->getConnection();

        $childCategory = $conn->query("SELECT * FROM orderlistextra WHERE orderid = $productOrderId")->fetch();

        return $childCategory;

    }

    public function getProducts($categoryId)
    {

        $conn = $this->getEntityManager()->getConnection();

        $products = $conn->query("SELECT * FROM products WHERE category_id = $categoryId AND deleted = 0 AND state = 1")->fetchAll();

        return $products;

    }

    public function getCategoryProducts($categoryId)
    {

        $conn = $this->getEntityManager()->getConnection();

        $categoryProducts = $conn->query("SELECT * FROM products WHERE category_id = $categoryId")->fetchAll();

        return $categoryProducts;

    }
    public function getCategoriesName($categoryIds)
    {

        $conn = $this->getEntityManager()->getConnection();

        $categories = $conn->query("SELECT name FROM categories WHERE id in ($categoryIds)")->fetchAll();

        return $categories;

    }


//    /**
//     * @return Categories[] Returns an array of Categories objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Categories
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
