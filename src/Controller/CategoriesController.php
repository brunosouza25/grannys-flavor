<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Entity\ProductsZoneSoft;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

/** @Route("/admin", name="admin/") */
class CategoriesController extends AbstractController
{

    /**
     * @Route("/get_categories", name="get_categories")
     */

    public function get_categories(ManagerRegistry $doctrine): Response
    {
        $en = $doctrine->getManager();

        $qb = $en->createQueryBuilder();

        $query = $qb->select('a.id, a.name, a.description, a.image, a.parent_id')
            ->from('App\Entity\Categories', 'a')
            ->where('a.state = 1')
            ->getQuery();

        $categories = $query->getArrayResult();
        $newCategories = [];
       foreach($categories as $category) {
           $category['line'] = '';

           $parentId = $category['parent_id'];

           if (!is_null($parentId)) {
               $category['line'] = '<b>Subcategoria</b> | ';
           }

           while(!is_null($parentId)) {
               $categoryParent = $doctrine->getRepository(Categories::class)->find($parentId);

               $category['line'] .= $categoryParent->getName().' / ';

               $parentId = $categoryParent->getParentId();

           }

//           if (!is_null($category['parent_id'])){
//               $idCategory = $category['parent_id'];
//
//               $end = false;
//               while (!$end) {
////                   dump($idCategory);
//                   $categoryParent = $doctrine->getRepository(Categories::class)->find($idCategory);
////                   dd($categoryParent);
//                   if(is_null($categoryParent->getParentId())) {
//                       $end = true;
//                   }
//
//                   if(!$end) {
//                       $category['line'] .= $categoryParent->getName()." /" ;
//                       dump($category['line']);
//                       $idCategory = $categoryParent->getParentId();
//                   }
//
//
//               }
//
//
//           }
           if(!empty($category['line'])){

               $category['line'] .= $category['name'];
           } else {
               $category['line'] = '<b>Categoria</b>';
           }


           $newCategories[] = $category;
       }

        return new JsonResponse($newCategories);
    }

//    /**
//     * @Route("/get_categories_menu", name="get_categories_menu")
//     */
//
//    public function get_categories_menu(ManagerRegistry $doctrine): Response
//    {
//        $en = $doctrine->getManager();
//
//        $qb = $en->createQueryBuilder();
//
//        $query = $qb->select('a.id, a.name, a.description, a.image, a.parent_id')
//            ->from('App\Entity\Categories', 'a')
//            ->where('a.state = 1')
//            ->groupBy('a.name')
//            ->getQuery();
//
//        $categories = $query->getArrayResult();
//
//
//        $newCategories = [];
//
//
//
//       foreach($categories as $category) {
//           $category['line'] = '';
//
//           $parentId = $category['parent_id'];
//
//           if (!is_null($parentId)) {
//               $category['line'] = '<b>Subcategoria</b> | ';
//           }
//
//           while(!is_null($parentId)) {
//               $categoryParent = $doctrine->getRepository(Categories::class)->find($parentId);
//
//               $category['line'] .= $categoryParent->getName().' / ';
//
//               $parentId = $categoryParent->getParentId();
//
//           }
////           if (!is_null($category['parent_id'])){
////               $idCategory = $category['parent_id'];
////
////               $end = false;
////               while (!$end) {
//////                   dump($idCategory);
////                   $categoryParent = $doctrine->getRepository(Categories::class)->find($idCategory);
//////                   dd($categoryParent);
////                   if(is_null($categoryParent->getParentId())) {
////                       $end = true;
////                   }
////
////                   if(!$end) {
////                       $category['line'] .= $categoryParent->getName()." /" ;
////                       dump($category['line']);
////                       $idCategory = $categoryParent->getParentId();
////                   }
////
////
////               }
////
////
////           }
//           if(!empty($category['line'])){
//
//               $category['line'] .= $category['name'];
//           } else {
//               $category['line'] = '<b>Categoria</b>';
//           }
//
//
//           $newCategories[] = $category;
//       }
//
//        return new JsonResponse($newCategories);
//    }

    /**
     * @Route("/delete_category", name="delete_category")
     */

    public function deleteCategory(ManagerRegistry $doctrine, Request $request): Response
    {

        $categoryId = $request->get('itemId');

        $en = $doctrine->getManager();

        $category = $doctrine->getRepository(Categories::class)->find($categoryId);
        $category->setState(0);

        $en->persist($category);
        $en->flush();

        return new Response();
    }


    /**
     * @Route("/edit_category", name="edit_category")
     */

    public function editCategory(ManagerRegistry $doctrine, Request $request): Response
    {

        $categoryId = $request->get('itemId');
        $categoryName = $request->get('itemName');
        $categoryDescription = $request->get('categoryDescription');

        /**
         * quando o parentid for vazio sistema ignora ele "nulo" como subcategoria e o usa como categoria pai
         */
        $categoryParentId = empty($request->get('categoryParent')) ? null : $request->get('categoryParent');


        $en = $doctrine->getManager();

        $category = $doctrine->getRepository(Categories::class)->find($categoryId);
        $category->setName($categoryName);
        $category->setDescription($categoryDescription);
        $category->setParentId($categoryParentId);
        /** IMAGEM */

        if (isset($_FILES['file']['name'])) {
            $filename = $_FILES['file']['name'];
            $location = "uploads/" . $filename;

            $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
            $imageFileType = strtolower($imageFileType);

            $valid_extensions = array("jpg", "jpeg", "png", "webp");


            if (in_array(strtolower($imageFileType), $valid_extensions)) {

                $src = $_FILES['file']['tmp_name'];
                $dest = "uploads/" . explode('.', $_FILES['file']['name'])[0] . '.webp';
                $destjpeg = "uploads/" . time() . "-" . $_FILES['file']['name'];

                $quality = 40;

                if (strtolower($imageFileType) != "webp") {
                    $info = getimagesize($src);

                    if ($info['mime'] == 'image/jpeg') {
                        $image = imagecreatefromjpeg($src);
                    } elseif ($info['mime'] == 'image/gif') {
                        $image = imagecreatefromgif($src);
                    } elseif ($info['mime'] == 'image/png') {
                        $image = imagecreatefrompng($src);
                    } else {
                        die('Unknown image file format');
                    }

                    //compress and save file to jpg
                    imagejpeg($image, $destjpeg, $quality);

                    // Create and save
                    $img = imagecreatefromjpeg($destjpeg);
                    imagepalettetotruecolor($img);
                    imagealphablending($img, true);
                    imagesavealpha($img, true);
                    imagewebp($img, $dest, 10);
                    imagedestroy($img);

                    unlink($destjpeg);
                    //return destination file

                } else {
                    $img = imagecreatefromwebp($src);

                    imagewebp($img, $dest, 10);
                }

//                copy($_FILES['file']['tmp_name'], $dest);

                $category->setImage($dest);

            }
        }

        /** FIM IMAGEM */




        $en->persist($category);
        $en->flush();

        return new Response();
    }

    /**
     * @Route("/get_category", name="get_category")
     */

    public function getCategory(ManagerRegistry $doctrine, Request $request): Response
    {

        $categoryId = $request->get('itemId');

        $en = $doctrine->getManager();

        $qb2 = $en->createQueryBuilder();

        $query = $qb2->select('a')
            ->from('App\Entity\Categories', 'a')
            ->where("a.id = $categoryId")
            ->getQuery();

        $category = $query->getArrayResult();

//        $category = $doctrine->getRepository(Categories::class)->find($categoryId);
        return new JsonResponse($category[0]);

    }

    /**
     * @Route("/create_new_category", name="create_new_category")
     */

    public function createNewCategory(ManagerRegistry $doctrine, Request $request): Response
    {
        $categoryName = $request->get('categoryName');
        $categoryDescription = $request->get('categoryDescription');
        $categoryParentId = empty($request->get('categoryParentId')) ? null : $request->get('categoryParentId');
        $en = $doctrine->getManager();

        $category = new Categories();
        $category->setName($categoryName);
        $category->setState(1);
        $category->setLevel(0);
        $category->setDescription($categoryDescription);
        $category->setParentId($categoryParentId);

        /** IMAGEM */

        if (isset($_FILES['file']['name'])) {
            $filename = $_FILES['file']['name'];
            $location = "uploads/" . $filename;

            $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
            $imageFileType = strtolower($imageFileType);

            $valid_extensions = array("jpg", "jpeg", "png", "webp");


            if (in_array(strtolower($imageFileType), $valid_extensions)) {

                $src = $_FILES['file']['tmp_name'];
                $dest = "uploads/" . explode('.', $_FILES['file']['name'])[0] . '.webp';
                $destjpeg = "uploads/" . time() . "-" . $_FILES['file']['name'];

                $quality = 40;

                if (strtolower($imageFileType) != "webp") {
                    $info = getimagesize($src);

                    if ($info['mime'] == 'image/jpeg') {
                        $image = imagecreatefromjpeg($src);
                    } elseif ($info['mime'] == 'image/gif') {
                        $image = imagecreatefromgif($src);
                    } elseif ($info['mime'] == 'image/png') {
                        $image = imagecreatefrompng($src);
                    } else {
                        die('Unknown image file format');
                    }

                    //compress and save file to jpg
                    imagejpeg($image, $destjpeg, $quality);

                    // Create and save
                    $img = imagecreatefromjpeg($destjpeg);
                    imagepalettetotruecolor($img);
                    imagealphablending($img, true);
                    imagesavealpha($img, true);
                    imagewebp($img, $dest, 10);
                    imagedestroy($img);

                    unlink($destjpeg);
                    //return destination file

                } else {
                    $img = imagecreatefromwebp($src);

                    imagewebp($img, $dest, 10);
                }

//                copy($_FILES['file']['tmp_name'], $dest);

                $category->setImage($dest);

            }
        }

        /** FIM IMAGEM */

        $en->persist($category);
        $en->flush();

        return new Response();
    }

    /**
     * @Route("/categories", name="categories")
     */

    public function index(ManagerRegistry $doctrine): Response
    {
        $en = $doctrine->getManager();

        $qb = $en->createQueryBuilder('p');
        $qb->select('c')
            ->from('App\Entity\Categories', 'c')
            ->orderBy('c.ordernr', 'ASC');

        $categories = $qb->getQuery()->getResult();

//        dd($categories);

        return $this->render('ADMIN/categories/index.html.twig', [
            'titlePage' => 'Produtos e Acompanhamentos',
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/get_edit_category", name="get_edit_category")
     */

    public function getEditCategory(ManagerRegistry $doctrine, Request $request): Response
    {
        $categoryId = $request->get('itemId');

        $category = $doctrine->getRepository(Categories::class)->find($categoryId);

        $en = $doctrine->getManager();

        $qb = $en->createQueryBuilder();

        $query = $qb->select('a.id, a.name, a.description, a.image, a.parent_id')
            ->from('App\Entity\Categories', 'a')
            ->where('a.state = 1')
            ->andWhere("a.id != $categoryId")
            ->getQuery();

        $categories = $query->getArrayResult();

        $ignore = [];

        if(!is_null($category->getParentId())) {

            $parentId = $category->getParentId();

            while(!is_null($parentId)) {

                $categoryParent = $doctrine->getRepository(Categories::class)->find($parentId);
                $ignore[] = $categoryParent->getId();
                $parentId = $categoryParent->getParentId();

            }
        }

        $categoriesChild = $doctrine->getRepository(Categories::class)->findBy(['parent_id' => $categoryId, 'state' => 1]);

        foreach ($categoriesChild as $categoryChild) {
            $ignore[] = $categoryChild->getId();
        }


//            dd($ignore);
//        $categories = $doctrine->getRepository(Categories::class)->findBy(['state' => 1]);

        return $this->render('ADMIN/categories/edit-category.html.twig', [
            'titlePage' => 'Produtos e Acompanhamentos',
            'category' => $category,
            'categories' => $categories,
            'ignore' => $ignore,

        ]);
    }
//
//    /**
//     * @Route("/subcategories", name="subcategories")
//     */
//
//    public function subcategories(ManagerRegistry $doctrine): Response
//    {
//        $en = $doctrine->getManager();
//
//        $qb = $en->createQueryBuilder('p');
//        $qb->select('c')
//            ->from('App\Entity\Categories', 'c')
//            ->orderBy('c.ordernr', 'ASC');
//
//        $categories = $qb->getQuery()->getResult();
//
////        dd($categories);
//
//        return $this->render('ADMIN/categories/index.html.twig', [
//            'titlePage' => 'Produtos e Acompanhamentos',
//            'categories' => $categories,
//        ]);
//    }

    /**
     * @Route("/new_category", name="new_category")
     */

    public function newCategory(ManagerRegistry $doctrine): Response
    {
        $categories = $doctrine->getRepository(Categories::class)->findBy(['state' => 1]);
        return $this->render('ADMIN/categories/create-category.html.twig',[
            'categories' => $categories
        ]);
    }
//    /**
//     * @Route("/categories", name="categories")
//     */
//
//    public function index(ManagerRegistry $doctrine): Response
//    {
//        return $this->render('ADMIN/categories/index.html.twig', [
//            'titlePage' => 'Categorias'
//        ]);
//    }


    /**
     * @Route("/get_categories_menu", name="get_categories_menu")
     */
    public function categories(EntityManagerInterface $entityManager)
    {
        $conn = $entityManager->getConnection();

        $sql = 'SELECT * FROM categories ORDER BY name';
        $stmt = $conn->query($sql);
//        $stmt->execute();
        $categories = $stmt->fetchAll();
        $categoryArray = [];

        foreach ($categories as $category) {
            if ($category['parent_id'] === null) {
                $categoryArray[] = [
                    'id' => $category['id'],
                    'name' => $category['name'],
                    'subcategories' => $this->getSubcategories($categories, $category['id'])
                ];
            }
        }


        return new JsonResponse($categoryArray);
    }

    private function getSubcategories($categories, $parentId)
    {
        $subcategoryArray = [];
        foreach ($categories as $category) {
            if ($category['parent_id'] == $parentId) {
                $subcategoryArray[] = [
                    'id' => $category['id'],
                    'name' => $category['name'],
                    'subcategories' => $this->getSubcategories($categories, $category['id'])
                ];
            }
        }

        return $subcategoryArray;
    }
}
