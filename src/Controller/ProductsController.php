<?php

namespace App\Controller;

use App\Controller\ADMIN\ConfigsController;
use App\Entity\Bore;
use App\Entity\Brand;
use App\Entity\Cart;
use App\Entity\Categories;
use App\Entity\Equipament;
use App\Entity\Families;
use App\Entity\FavoriteProducts;
use App\Entity\Foodmenuitemsmultiple;
use App\Entity\Grid;
use App\Entity\Guestcontact;
use App\Entity\Images;
use App\Entity\Material;
use App\Entity\Models;
use App\Entity\Products;
use App\Entity\ProductsGrid;
use App\Entity\ProductsImage;
use App\Entity\ProductsTags;
use App\Entity\ProductsZoneSoft;
use App\Entity\Subfamilie;
use App\Entity\SystemConfig;
use App\Entity\Tags;
use App\Entity\Type;
use App\Service\CategoriesService;
use App\Service\GuestContactService;
use App\Service\OrderCartService;
use App\Service\ProductsService;
use App\Service\SessionService;
use App\Service\VouchersService;
use Doctrine\ORM\Mapping\Driver\RepeatableAttributeCollection;
use Doctrine\ORM\Query\AST\Join;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ProductsController extends AbstractController
{
    private $categoriesSerice;
    private $productsService;
    private $guestContactService;
    private $sessionService;
    private $orderCartService;
    private $vouchersService;

    public function __construct(
        CategoriesService $categoriesSerice,
        ProductsService $productsService,
        GuestContactService $guestContactService,
        SessionService $sessionService,
        OrderCartService $orderCartService,
        VouchersService $vouchersService
    )
    {
        $this->categoriesSerice = $categoriesSerice;
        $this->productsService = $productsService;
        $this->guestContactService = $guestContactService;
        $this->sessionService = $sessionService;
        $this->orderCartService = $orderCartService;
        $this->vouchersService = $vouchersService;
    }

    /**
     * @Route("/get_products", name="get_products")
     */

    public function getProducts(ManagerRegistry $doctrine): Response
    {
        $en = $doctrine->getManager();

        $qb2 = $en->createQueryBuilder();

        $query = $qb2->select('a.name, a.price, a.image, b.name as nameCategory')
            ->from('App\Entity\Products', 'a')
            ->innerJoin('App\Entity\Categories', 'b', 'WITH', 'a.category_id = b.id')
            ->where('a.deleted = 0')
            ->getQuery();

        $products = $query->getArrayResult();

        return $this->render('products/index.html.twig', [
            'titlePage' => 'Produtos',
        ]);
    }

    /**
     * @Route("/get_products_Filtered", name="get_products_Filtered")
     */

    public function getProductsFiltered(ManagerRegistry $doctrine, Request $request): Response
    {


        //dd($request->get('selectedFilters'));
        $en = $doctrine->getManager();
        $conn = $en->getConnection();


        $filters = json_decode(stripslashes(base64_decode($request->get('selectedFilters'))));


        $query = 'SELECT p.name AS name, p.id as id, p.image as image, p.price as price, c.name as categoryName
                                    FROM products as p
                                             INNER JOIN categories AS c
                                                        ON p.category_id = c.id
                                    WHERE p.state = 1
                                      and p.deleted = 0';

        $query = 'SELECT brand.name as brandName, p.name AS name, p.id as id, p.image as image, p.price as price, c.name as categoryName
                                    FROM products as p
                                             INNER JOIN categories AS c
                                                        ON p.category_id = c.id
                                            INNER JOIN brand AS brand ON brand.id = p.brand_id
                                    WHERE p.state = 1
                                      and p.deleted = 0';


        foreach ($filters as $filter) {
            if (count($filter->selecteds) > 0) {

                if ($filter->filter == 'brands') {
                    $filter->filter = 'brand';
                }

                $ids = implode(',', $filter->selecteds);
                $query .= " AND $filter->filter.id in ($ids)";
            }
        }

        $productsFiltered = $conn->query($query)->fetchAll();

        return new JsonResponse(
            $productsFiltered
        );
    }

    /**
     * @Route("/store")
     */

    public function Products(ManagerRegistry $doctrine, Request $request): Response
    {

        $search = $request->get('search');

        $category = $request->get('categoryId');
        $filters = $request->get('filter');

        $filters = $request->get('filter');

        $en = $doctrine->getManager();
        $conn = $en->getConnection();

        $query = 'SELECT MAX(price) AS maxPrice, MIN(price) AS minPrice FROM products;';

        $rangeInfo = $conn->query("$query")->fetch();

        return $this->render('default/index2.html.twig', [
            'titlePage' => 'Store',
            'filters' => isset($filters) ? $filters : '',
            'search' => isset($search) ? $search : '',
            'categoryId' => isset($category) ? $category : '',
            'minPrice' => $rangeInfo['minPrice'],
            'maxPrice' => $rangeInfo['maxPrice'],
        ]);
    }

    /**
     * @Route("/productscategory")
     */

    public function productscategory(ManagerRegistry $doctrine, Request $request): Response
    {

        dd($this->categoriesSerice->getCategoryAndChildrenProducts(3, []));

    }

    /**
     * @Route("/admin/change_product_high", name="admin/change_product_high")
     */

    public function changeProductHigh(ManagerRegistry $doctrine, Request $request): Response
    {

        $en = $doctrine->getManager();
        $productId = $request->get('productId');

        $product = $doctrine->getRepository(Products::class)->find($productId);

        $product->setHighlight(!$product->isHighlight());

        $en->persist($product);
        $en->flush();


        return new Response();
    }


    /**
     * @Route("/get_products_high")
     */

    public function productsHigh(ManagerRegistry $doctrine): Response
    {
        $en = $doctrine->getManager();
        $conn = $en->getConnection();

        $query = 'SELECT p.id AS id, p.name AS name, p.price AS price, p.image AS image, p.stock as stock, p.grid as grid, c.name AS categoryName 
              FROM products AS p 
              INNER JOIN categories AS c ON p.category_id = c.id 
              WHERE p.highlight = 1 AND p.deleted = 0 AND p.state = 1';

        $products = $conn->query($query)->fetchAll();

        $newProducts = [];

        foreach ($products as $product) {
            if (!is_object($product)) {
                $product = (object)$product;
            }

            $product->gridColors = $this->productsService->getColorsGridProduct($product->id);
            $userId = 0;

            // Check if the product is a favorite for the user
            if (!is_null($this->guestContactService->getUserSession())) {
                $userId = $this->guestContactService->getUserSession()->getId();
            }

            $product->isFavorite = $this->isProductFavorite($product->id, $userId);

            if ($product->grid == 1) {
                //$product->stock = $this->productsService->productGridStockSum($product->id)['stock'];
                $product->stock = 999999;
            }

            $newProducts[] = $product;
        }
        //dd($newProducts);
        return new JsonResponse($newProducts);
    }

    private function isProductFavorite(int $productId, int $userId): bool
    {
        $query = 'SELECT COUNT(*) AS count FROM favorite_products WHERE products_id = :productId AND user_id = :userId';
        $params = ['productId' => $productId, 'userId' => $userId];

        $result = $this->getDoctrine()->getConnection()->fetchAssociative($query, $params);

        return $result['count'] > 0;
    }


    /**
     * @Route("/products", name="products")
     */

    public function index(ManagerRegistry $doctrine, Request $request): Response
    {
        $en = $doctrine->getManager();
        $conn = $en->getConnection();
        $search = $request->get('search');
        $category = $request->get('categoryId');

        $columns = "SELECT c.id as categories_id, p.tag_id AS tag_id, p.bore_id as bore_id, p.brand_id as brand_id, p.type_id as type_id , p.grid as grid, p.name AS name, p.id as id, p.image as image, p.price as price, p.stock as stock, c.name as categoryName";
        $tables = " FROM products as p
                                             INNER JOIN categories AS c
                                                        ON p.category_id = c.id";

        $wheres = " WHERE p.state = 1 and p.deleted = 0";
        if ($category != '') {

            $categoryIds = implode(',', $this->categoriesSerice->getCategoryAndChildrenIds($category, []));

            $wheres .= " AND c.id IN ($categoryIds)";
        }

        if ($search != '') {
            $wheres .= " AND (p.name like '%$search%' OR p.id = '$search' OR p.code like '%$search%') ";
        }

        if (!is_null($request->get('filtersProduct')) && $request->get('filtersProduct')) {


            $filters = json_decode(stripslashes(base64_decode($request->get('filtersProduct'))));
            foreach ($filters as $filter) {
                if ($filter->filter == 'colors' || $filter->filter == 'sizes') {
                    $tables .= " INNER JOIN products_grid AS pg ON p.id = pg.product_id";
                    break;
                }
            }

            foreach ($filters as $filter) {
                if ($filter->filter == 'price' || $filter->filter == 'price-mobile') {
                    $minPrice = $filter->prices->minPrice;
                    $maxPrice = $filter->prices->maxPrice;

                    $wheres .= " AND p.price BETWEEN $minPrice AND $maxPrice ";

                } else {
                    if (count($filter->selecteds) > 0) {

                        if ($filter->filter == 'brands' || $filter->filter == 'brands-mobile') {
                            $filter->filter = 'brand';
                        }

                        if ($filter->filter == 'types' || $filter->filter == 'types-mobile') {
                            $filter->filter = 'type';
                        }

                       /* if ($filter->filter == 'tags' || $filter->filter == 'tags-mobile') {
                            $filter->filter = 'tag';
                        }

                        if ($filter->filter == 'bores' || $filter->filter == 'bores-mobile') {
                            $filter->filter = 'bore';
                        }

                        if ($filter->filter == 'equipaments' || $filter->filter == 'equipaments-mobile') {
                           $filter->filter = 'equipament';
                        }

                        if ($filter->filter == 'models' || $filter->filter == 'models-mobile') {
                            //dd('filter-model');
                            $filter->filter = 'model';
                        }

                        if ($filter->filter == 'sizes' || $filter->filter == 'sizes-mobile') {
                            $filter->filter = 'size';
                        }

                        if ($filter->filter == 'colors' || $filter->filter == 'colors-mobile') {
                            $filter->filter = 'color';
                        }*/

                        if ($filter->filter == 'categories-mobile') {
                            $filter->filter = 'categories';
                        }

                        if ($filter->filter == 'categories') {
                            $categoriesIds = [];

                            foreach ($filter->selecteds as $category) {
                                $categoriesIds = array_merge($categoriesIds, $this->categoriesSerice->getCategoryAndChildrenIds($category, []));
                            }

                            $categoriesIds = implode(',', $categoriesIds);
                            $wheres .= " AND c.id in ($categoriesIds)";

                        } else {
                            $ids = implode(',', $filter->selecteds);
                            if ($filter->filter == 'color' || $filter->filter == 'size' || $filter->filter == 'color-mobile' || $filter->filter == 'size-mobile') {

                                $wheres .= " AND pg.grid_$filter->filter" . "_id in ($ids) ";
                            } else {
                                $wheres .= " AND $filter->filter" . "_id in ($ids)";
                            }
                        }


                    }
                }

            }

        }


        $query = $columns . $tables . $wheres;
        $products = $conn->query($query)->fetchAll();

        $userId = 0;
        if (!is_null($this->guestContactService->getUserSession())) {
            $userId = $this->guestContactService->getUserSession()->getId();
        }

//        foreach ($products as $product) {
//        }

        $array = new \stdClass();

        $newProducts = [];

        foreach ($products as $product) {
            if (!is_object($product)) {
                $product = (object)$product;
            }

            $product->gridColors = $this->productsService->getColorsGridProduct($product->id);
            //dd($product);
            if ($product->grid == 1) {
                //$product->stock = $this->productsService->productGridStockSum($product->id)['stock'];
                $product->stock = 99999;
            }
            //SETANDO STOCK ESTÁTICO
            $product->stock = 999999;
            $product->isFavorite = $this->isProductFavorite($product->id, $userId);

            //dd($product);
            $newProducts[] = $product;
        }
        $array->products = $newProducts;

        $searchName = 'false';

        if ($category != '') {
            $searchName = $doctrine->getRepository(Categories::class)->find($category)->getName();
        } else {
            if ($search != '') {
                $searchName = $search;
            }
        }
        $array->searchName = $searchName;
        //dd($array);
        return new JsonResponse($array);
    }

    /**
     * @Route("/product/{product_id}", name="product")
     */

    public function productInformation(ManagerRegistry $doctrine, Request $request): Response
    {

        $productId = $request->get('product_id');
        $name = '';
        $session = $this->sessionService->checkSession();

        $costumer = $doctrine->getRepository(Guestcontact::class)->findOneBy(['session' => $session]);
        if (!is_null($costumer)) {

            $name = $costumer->getName();
        }


        return $this->render('products/product-information.html.twig', [
            'titlePage' => 'Produtos',
            'productId' => $productId,
            'name' => $name
        ]);
    }

    /**
     * @Route("/cart/", name="cart")
     */

    public function cart(ManagerRegistry $doctrine, Request $request): Response
    {
        $name = '';
        $session = $this->sessionService->checkSession();
        $costumer = $doctrine->getRepository(Guestcontact::class)->findOneBy(['session' => $session]);
        if (!is_null($costumer)) {

            $name = $costumer->getName();
        }


        return $this->render('products/cart.html.twig', [
            'titlePage' => 'Carrinho',
            'name' => $name
        ]);
    }

    /**
     * @Route("/admin/products", name="admin/products")
     */

    public function adminProducts(ManagerRegistry $doctrine): Response
    {
        $en = $doctrine->getManager();

        $listProducts = $doctrine->getRepository(Products::class)->findBy(['deleted' => 0]);

        return $this->render('ADMIN/products/index.html.twig', [
            'titlePage' => 'Lista de Produtos',
            'products' => $listProducts
        ]);
    }

    /**
     * @Route("/admin/edit_product", name="admin/edit_product")
     */

    public function editProduct(ManagerRegistry $doctrine, Request $request): Response
    {
        //dd($request->get('productId'));
        $en = $doctrine->getManager();
        $productId = $request->get('productId');
        $zoneSoftCode = $request->get('zonesoftcode');

        //$productTags = $request->get('tags');
        //$productEquipament = $request->get('equipamentId');

        $product = $doctrine->getRepository(Products::class)->find($productId);

        $product->setName($request->get('productName'));

        $price = str_replace(",", ".", $request->get('productPrice'));
        $price = preg_replace('/\.(?=.*\.)/', '', $price);
        $product->setPrice((double)$price);

        $product->setCategoryId($request->get('categoryId'));
        $product->setBrandId($request->get('brandId'));
        //$product->setMaterialId($request->get('materialId'));
        //$product->setBoreId($request->get('boreId'));
        //$product->setTypeId($request->get('typeId'));
        //$product->setModelId($request->get('modelId'));

        //dd($request->get('modelId'));
        $product->setDescription(trim($request->get('productDescription')));
        $product->setCode($request->get('productCode'));
        $product->setZonesoftcode($zoneSoftCode);
        //$product->setEquipamentId($productEquipament);
        $product->setIva($request->get('productIva'));

//        dd($product->getPrice());

        /** IMAGEM */

        if (isset($_FILES['file']['name'])) {
            $filename = $_FILES['file']['name'];
            $location = "uploads/" . $filename;

            $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
            $imageFileType = strtolower($imageFileType);

            $valid_extensions = array("jpg", "jpeg", "png", "webp", "jfif");


            if (in_array(strtolower($imageFileType), $valid_extensions)) {

                $src = $_FILES['file']['tmp_name'];
                $dest = "uploads/" . explode('.', $_FILES['file']['name'])[0] . "-" . date("Y-m-d-h:i:s") . '.webp';
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

                $currenctimg = $product->getImage();

                if (file_exists($currenctimg)) {
                    unlink($currenctimg);
                }

//                copy($_FILES['file']['tmp_name'], $dest);

                $product->setImage($dest);
            }
        }
        //dd($product);
        $en->persist($product);
        /** FIM IMAGEM */

        //$productTagsToRemove = $doctrine->getRepository(ProductsTags::class)->findBy(['product_id' => $productId]);

        /*foreach ($productTagsToRemove as $productTagToRemove) {
            $en->remove($productTagToRemove);
        }*/

        //$en->flush();

       /* foreach ((array)$productTags as $productTag) {
            $newProductTag = new ProductsTags();
            $newProductTag->setProductId($productId);
            $newProductTag->setTagId($productTag);
            $en->persist($newProductTag);
        }*/

        $en->flush();


        return new Response();
    }


    /**
     * @Route("/admin/change_product_main_image", name="admin/change_product_main_image")
     */
    public function changeProductMainImage(ManagerRegistry $doctrine, Request $request): Response
    {
        $en = $doctrine->getManager();
        $productImage = $doctrine->getRepository(ProductsImage::class)->find($request->get('productImageId'));
        $productImage->setMainImage(1);
        $en->persist($productImage);

        $productMainImage = $doctrine->getRepository(ProductsImage::class)->findOneBy(['product_id' => $productImage->getProductId(), 'main_image' => 1]);
        $productMainImage->setMainImage(0);
        $en->persist($productMainImage);

        $product = $doctrine->getRepository(Products::class)->find($productImage->getProductId());
        $product->setImage($productImage->getPath());
        $en->persist($product);

        $en->flush();


        return new Response();
    }

    /**
     * @Route("/products_range_value", name="products_range_value")
     */
    public function productsRangeValue(ManagerRegistry $doctrine, Request $request): Response
    {
        $en = $doctrine->getManager();
        $conn = $en->getConnection();

        $query = 'SELECT MAX(price) AS maxPrice, MIN(price) AS minPrice FROM products;';

        $rangeInfo = $conn->query("$query")->fetchAll();

        return new JsonResponse($rangeInfo);
    }

    /**
     * @Route("/admin/delete_product_image", name="admin/delete_product_image")
     */
    public function deleteProductMainImage(ManagerRegistry $doctrine, Request $request): Response
    {
        $en = $doctrine->getManager();
        $productImageId = $doctrine->getRepository(ProductsImage::class)->find($request->get('productImageId'));
        unlink($productImageId->getPath());
        $productId = $productImageId->getProductId();

        $en->remove($productImageId);
        $en->flush();

        $productImageMain = $doctrine->getRepository(ProductsImage::class)->findOneBy(['product_id' => $productId, 'main_image' => 1]);
//            dd(!empty($newProductImageMain));
        if (empty($productImageMain)) {
            $newProductImageMain = $doctrine->getRepository(ProductsImage::class)->findOneBy(['product_id' => $productId]);
            if (!empty($newProductImageMain)) {
                $newProductImageMain->setMainImage(1);

                $product = $doctrine->getRepository(Products::class)->find($newProductImageMain->getProductId());
                $product->setImage($newProductImageMain->getPath());
                $en->persist($product);

                $en->persist($newProductImageMain);
                $en->flush();

            } else {
                $product = $doctrine->getRepository(Products::class)->find($productId);
                $product->setImage('');
                $en->persist($product);
                $en->flush();

            }

        }


        return new Response();
    }

    /**
     * @Route("/admin/desactive_active_product", name="admin/desactive_active_product")
     */

    public function desactiveActiveProduct(ManagerRegistry $doctrine, Request $request): Response
    {

        $en = $doctrine->getManager();
        $productId = $request->get('productId');

        $product = $doctrine->getRepository(Products::class)->find($productId);

        $product->setState(!$product->getState());

        $en->persist($product);
        $en->flush();


        return new Response();
    }

    /**
     * @Route("/admin/load_zone_soft_codes", name="admin/load_zone_soft_codes")
     */

    public function loadZoneSoftCodes(ManagerRegistry $doctrine, Request $request): Response
    {
        $en = $doctrine->getManager();

        $conn = $en->getConnection();

        $zoneSoftProducts = $conn->query("SELECT zone_soft_code FROM products_zone_soft")->fetchAll();
        return new JsonResponse($zoneSoftProducts);
    }

    /**
     * @Route("/admin/delete_product", name="admin/delete_product")
     */

    public function deleteProduct(ManagerRegistry $doctrine, Request $request): Response
    {

        $en = $doctrine->getManager();
        $productId = $request->get('productId');

        $product = $doctrine->getRepository(Products::class)->find($productId);

        $product->setDeleted(1);
        $product->setState(0);

        $en->persist($product);
        $en->flush();


        return new Response();
    }

    /**
     * @Route("/admin/create_new_product", name="admin/create_new_product")
     */

    public function createNewProduct(ManagerRegistry $doctrine, Request $request): Response
    {


        $en = $doctrine->getManager();
        $productTags = $request->get('tags');

        $product = new Products();

        $product->setName($request->get('productName'));

        $price = str_replace(",", ".", $request->get('productPrice'));
        $price = preg_replace('/\.(?=.*\.)/', '', $price);
        $product->setPrice((double)$price);

        if (!is_null($request->get('categoryId'))) {
            $product->setCategoryId($request->get('categoryId'));

        }

        $product->setType(0);
        $product->setGrid(0);
        $product->setState(1);
        $product->setDeleted(0);
        $product->setBrandId($request->get('brandId'));
        $product->setMaterialId($request->get('materialId'));
        $product->setBoreId($request->get('boreId'));
        $product->setTypeId($request->get('typeId'));
        $product->setModelId($request->get('modelId'));
        $product->setDescription(trim($request->get('productDescription')));
        $product->setCode($request->get('productCode'));
        $product->setIva($request->get('productIva'));
        $product->setZonesoftcode($request->get('zonesoftcode'));
        $product->setStock(999999);

        $images = [];
        for ($i = 0; $i < count($_FILES['files']['name']); $i++) {
            $image = new \stdClass();
            $image->name = $_FILES['files']['name'][$i];
//            $image->full_path = $_FILES['files']['full_path'][$i];
            $image->type = $_FILES['files']['type'][$i];
            $image->tmp_name = $_FILES['files']['tmp_name'][$i];
            $image->error = $_FILES['files']['error'][$i];
            $images[] = $image;
        }

        /** tem de se remover */
        /** */
        /** IMAGEM */

        if (isset($images)) {
            foreach ($images as $key => $productImage) {
                $filename = $productImage->name;
                $location = "uploads/" . $filename;

                $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
                $imageFileType = strtolower($imageFileType);

                $valid_extensions = array("jpg", "jpeg", "png", "webp", "jfif");


                if (in_array(strtolower($imageFileType), $valid_extensions)) {

                    $src = $productImage->tmp_name;
                    $dest = "uploads/" . explode('.', $productImage->name)[0] . "-" . time() . '.webp';
                    $destjpeg = "uploads/" . time() . "-" . $productImage->name;

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
                    $productImage->newPath = $dest;
//                copy($_FILES['file']['tmp_name'], $dest);
                    if ($key == 0) {
                        $product->setImage($dest);
                    }

                }
            }
        }

        /** FIM IMAGEM */

        $en->persist($product);
        $en->flush();


        $en->flush();

        foreach ((array)$productTags as $productTag) {
            $newProductTag = new ProductsTags();
            $newProductTag->setProductId($product->getId());
            $newProductTag->setTagId($productTag);
            $en->persist($newProductTag);
        }

        $en->flush();


        foreach ($images as $key => $image) {
            $productImage = new ProductsImage();
            $productImage->setProductId($product->getId());
            $productImage->setPath($image->newPath);
            $productImage->setMainImage(0);

            if ($key == 0) {
                $productImage->setMainImage(1);
            }

            $en->persist($productImage);

        }
        $en->flush();

        return new Response();
    }

    /**
     * @Route("/admin/new_product_images", name="admin/new_product_images")
     */

    public function newProductImages(ManagerRegistry $doctrine, Request $request): Response
    {

        $en = $doctrine->getManager();
        $productId = $request->get('productId');
        $images = [];
        for ($i = 0; $i < count($_FILES['files']['name']); $i++) {
            $image = new \stdClass();
            $image->name = $_FILES['files']['name'][$i];
//            $image->full_path = $_FILES['files']['full_path'][$i];
            $image->type = $_FILES['files']['type'][$i];
            $image->tmp_name = $_FILES['files']['tmp_name'][$i];
            $image->error = $_FILES['files']['error'][$i];
            $images[] = $image;
        }

        /** tem de se remover */
        /** */
        /** IMAGEM */

        if (isset($images)) {
            foreach ($images as $key => $productImage) {
                $filename = $productImage->name;
                $location = "uploads/" . $filename;

                $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
                $imageFileType = strtolower($imageFileType);

                $valid_extensions = array("jpg", "jpeg", "png", "webp", "jfif");


                if (in_array(strtolower($imageFileType), $valid_extensions)) {

                    $src = $productImage->tmp_name;
                    $dest = "uploads/" . explode('.', $productImage->name)[0] . "-" . time() . '.webp';
                    $destjpeg = "uploads/" . time() . "-" . $productImage->name;

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
                    $productImage->newPath = $dest;
//                copy($_FILES['file']['tmp_name'], $dest);

                }
            }
        }

        /** FIM IMAGEM */

        $productImages = $doctrine->getRepository(ProductsImage::class)->findBy(['product_id' => $productId]);
        $mainImage = 0;
        if (empty($productImages)) {
            $mainImage = 1;
        }

        foreach ($images as $image) {
            $productImage = new ProductsImage();
            $productImage->setProductId($productId);
            $productImage->setPath($image->newPath);
            $productImage->setMainImage(0);

            if ($mainImage == 1) {
                $productImage->setMainImage(1);
                $product = $doctrine->getRepository(Products::class)->find($productId);
                $product->setImage($productImage->getPath());
                $en->persist($product);
                $en->flush();

                $mainImage = 0;
            }

            $en->persist($productImage);

        }
        $en->flush();

        return new Response();
    }


    /**
     * @Route("admin/product_detail", name="admin/app_product_detail")
     */
    public function productDetail(ManagerRegistry $doctrine, Request $request): Response
    {

        $en = $doctrine->getManager();

        $productID = $request->get('productId');
//        dd($request->get('productId'));

        $product = $doctrine->getRepository(Products::class)->find($productID);
        //dd($product);
        $zoneSoftProducts = $doctrine->getRepository(ProductsZoneSoft::class)->findAll();

        $categories = $doctrine->getRepository(Categories::class)->findBy(['state' => 1]);

        $brands = $doctrine->getRepository(Brand::class)->findBy(['status' => 1]);

        $materials = $doctrine->getRepository(Material::class)->findBy(['status' => 1]);

        //$bores = $doctrine->getRepository(Bore::class)->findBy(['status' => 1]);

        $types = $doctrine->getRepository(Type::class)->findBy(['status' => 1]);

        $models = $doctrine->getRepository(Models::class)->findBy(['status' => 1]);

        //$tags = $doctrine->getRepository(Tags::class)->findBy(['status' => 1]);
        $equipaments = $doctrine->getRepository(Equipament::class)->findBy(['status' => 1]);
        //dd($equipaments);
        /*foreach ($tags as $tag) {
            $productTag = $doctrine->getRepository(ProductsTags::class)->findBy(['product_id' => $productID, 'tag_id' => $tag->getId()]);
            if (!empty($productTag)) {
                $tag->active = 1;
            } else {
                $tag->active = 0;
            }
        }*/


        return $this->render('ADMIN/products/product-detail.html.twig', [
            'titlePage' => $product->getDescription(),
            'product' => $product,
            'zoneSoftProducts' => $zoneSoftProducts,
            'categories' => $categories,
            'brands' => $brands,
            'materials' => $materials,
            //'bores' => $bores,
            'types' => $types,
            'models' => $models,
            'zoneSoftCode' => is_null($product->getZonesoftcode()) ? -1 : $product->getZonesoftcode(),
            //'tags' => $tags,
            'equipaments' => $equipaments,
//            'subfamilie' => $subfamilieN,
        ]);
    }

    /**
     * @Route("/change_product_grid_status")
     */
    public function changeProductGridStatus(ManagerRegistry $doctrine, Request $request): Response
    {
        $productGridId = $request->get('productGridId');
        $this->productsService->changeProductGridStatus($productGridId);
        return new Response();
    }

    /**
     * @Route("admin/product_grid/{productId}", name="admin/product_grid")
     */
    public function productGrids(ManagerRegistry $doctrine, Request $request): Response
    {
        $productId = $request->get('productId');

        $en = $doctrine->getManager();

        $qb2 = $en->createQueryBuilder();

        $query = $qb2->select('a')
            ->from('App\Entity\Grid', 'a')
            ->where("a.status = 1")
            ->getQuery();

        $grids = $query->getArrayResult();


        return $this->render('ADMIN/products/product-grids.html.twig', [
            'grids' => $grids,
            'productId' => $productId
        ]);
    }


    /**
     * @Route("/get_product/{productId}", name="get_product")
     */
    public function getProduct(ManagerRegistry $doctrine, Request $request): Response
    {
        $productId = $request->get('productId');

        $en = $doctrine->getManager();

        $qb2 = $en->createQueryBuilder();

        $query = $qb2->select('p.grid, p.name ,p.price, p.id, p.code, p.description, p.image, p.stock , c.name as categoryName, b.name as brandName, t.name as typeName, bor.name as boreName')
            ->from('App\Entity\Products', 'p')
            ->innerJoin('App\Entity\Categories', 'c', 'WITH', "p.category_id = c.id")
            ->leftJoin('App\Entity\Brand', 'b', 'WITH', "b.id = p.brand_id")
            ->leftJoin('App\Entity\Type', 't', 'WITH', "t.id = p.type_id")
            ->leftJoin('App\Entity\Bore', 'bor', 'WITH', "bor.id = p.bore_id")
            ->where("p.id = $productId")
            ->getQuery();


        $product = $query->getArrayResult();

        $qbTags = $en->createQueryBuilder();

        $queryTags = $qbTags->select('t.name')
            ->from('App\Entity\ProductsTags', 'pt')
            ->innerJoin('App\Entity\Products', 'p', 'WITH', "pt.product_id = p.id")
            ->innerJoin('App\Entity\Tags', 't', 'WITH', "t.id = pt.tag_id")
            ->where("p.id = $productId")
            ->getQuery();


        $tags = $queryTags->getArrayResult();
        $productObject = (object)$product[0];
        $productObject->tags = $tags;

        return new JsonResponse($productObject);
    }

    /**
     * @Route("admin/product_images/{productId}", name="admin/product_images")
     */
    public function productImages(ManagerRegistry $doctrine, Request $request): Response
    {
        $productId = $request->get('productId');

        $en = $doctrine->getManager();

        $qb2 = $en->createQueryBuilder();

        $query = $qb2->select('a')
            ->from('App\Entity\ProductsImage', 'a')
            ->where("a.product_id = $productId")
            ->getQuery();

        $productImagens = $query->getArrayResult();

        return $this->render('products/product-images.html.twig', [
            'productImagens' => $productImagens,
            'productId' => $productId

        ]);
    }

    /**
     * @Route("admin/get_product_grids/{productId}", name="admin/get_product_grids")
     */
    public function getProductGrids(ManagerRegistry $doctrine, Request $request): Response
    {
        $productId = $request->get('productId');
        $en = $doctrine->getManager();

        $conn = $en->getConnection();

        $productGrids = $conn->query("SELECT pd.status as status, g.id AS grid_id, g.name AS name, pd.id as product_grid_id, pd.product_id as product_id, g.type as type FROM products_grid as pd
                               INNER JOIN grid AS g
                               ON pd.grid_color_id = g.id or pd.grid_size_id = g.id
                               WHERE pd.product_id = $productId and g.status = 1 group by grid_id")->fetchAll();

        return new JsonResponse($productGrids);


    }

    /**
     * @Route("/get_product_images/{productId}", name="get_product_images")
     */
    public function getProductImages(ManagerRegistry $doctrine, Request $request): Response
    {
        $productId = $request->get('productId');

        $en = $doctrine->getManager();

        $qb2 = $en->createQueryBuilder();

        $query = $qb2->select('a')
            ->from('App\Entity\ProductsImage', 'a')
            ->where("a.product_id = $productId")
            ->getQuery();
        $productImagens = $query->getArrayResult();
//        dd($productImagens);

        return new JsonResponse(
            $productImagens
        );

    }

    /**
     * @Route("/admin/new_product", name="admin/new_product")
     */
    public function newProductTemplate(ManagerRegistry $doctrine): Response
    {

        $zoneSoftProducts = $doctrine->getRepository(ProductsZoneSoft::class)->findAll();

        $categories = $doctrine->getRepository(Categories::class)->findBy(['state' => 1]);

        $brands = $doctrine->getRepository(Brand::class)->findBy(['status' => 1]);

        $materials = $doctrine->getRepository(Material::class)->findBy(['status' => 1]);

        //$bores = $doctrine->getRepository(Bore::class)->findBy(['status' => 1]);

        $types = $doctrine->getRepository(Type::class)->findBy(['status' => 1]);

        $models = $doctrine->getRepository(Models::class)->findBy(['status' => 1]);

        //$tags = $doctrine->getRepository(Tags::class)->findBy(['status' => 1]);


        return $this->render('ADMIN/products/create-product.html.twig', [
            'titlePage' => 'Novo produto',
            'zoneSoftProducts' => $zoneSoftProducts,
            'categories' => $categories,
            'brands' => $brands,
            'materials' => $materials,
            //'bores' => $bores,
            'types' => $types,
            'models' => $models,
            //'tags' => $tags,
//            'subfamilie' => $subfamilieN,
        ]);
    }

    /**
     * @Route("/get_colors/{productId}", name="get_colors")
     */
    public function getProductColors(ManagerRegistry $doctrine, Request $request): Response
    {
        $productId = $request->get('productId');
        $en = $doctrine->getManager();

        $conn = $en->getConnection();

        $grids = $conn->query("SELECT g.id AS grid_id, g.name AS name, g.image as image_color, pd.id as product_grid_id FROM products_grid as pd
                               INNER JOIN grid AS g
                               ON pd.grid_color_id = g.id
                               WHERE pd.product_id = $productId and g.status = 1 group by grid_id")->fetchAll();

        return new JsonResponse($grids);
    }

    /**
     * @Route("/get_sizes/{productId}", name="get_sizes")
     */
    public function getProductSizes(ManagerRegistry $doctrine, Request $request): Response
    {
        $productId = $request->get('productId');
        $en = $doctrine->getManager();

        $conn = $en->getConnection();

        $grids = $conn->query("SELECT g.id AS grid_id, g.name AS name, pd.id as product_grid_id FROM products_grid as pd
                               INNER JOIN grid AS g
                               ON pd.grid_size_id = g.id
                               WHERE pd.product_id = $productId and g.status = 1 group by grid_id")->fetchAll();

        return new JsonResponse($grids);
    }

    /**
     * @Route("/get_product_by_search", name="get_product_by_search")
     */
    public function getProductBySearch(ManagerRegistry $doctrine, Request $request): Response
    {
        $productId = $request->get('searchInput');
        $en = $doctrine->getManager();

        $conn = $en->getConnection();

        $grids = $conn->query("SELECT g.id AS grid_id, g.name AS name, pd.id as product_grid_id FROM products_grid as pd
                               INNER JOIN grid AS g
                               ON pd.grid_id = g.id
                               WHERE pd.product_id = $productId and g.status = 1 and g.type = 0")->fetchAll();

        return new JsonResponse($grids);
    }

    /**
     * @Route("/delete_product_cart", name="delete_product_cart")
     */
    public function deleteProductCart(ManagerRegistry $doctrine, Request $request): Response
    {
        $productCartId = $request->get('productCartId');
        $productCart = $doctrine->getRepository(Cart::class)->find($productCartId);
        $doctrine->getManager()->remove($productCart);
        $doctrine->getManager()->flush($productCart);

        return new Response();
    }

    /**
     * @Route("/get_quantity_products_cart", name="get_quantity_products_cart")
     */
    public function loadQuantityProductsCart(ManagerRegistry $doctrine, Request $request): Response
    {
        $session = $this->sessionService->checkSession();

//        $costumer = $doctrine->getRepository(Guestcontact::class)->findOneBy(['session' => $session]);
//
//        if(is_null($costumer)){
//            return new Response(0);
//        }

        $products = $doctrine->getRepository(Cart::class)->findBy(['session' => $session]);

        return new Response(count($products));
    }

    /**
     * @Route("/add_product_to_cart", name="add_product_to_cart")
     */
    public function add_product_to_cart(ManagerRegistry $doctrine, Request $request): Response
    {
        $product = (object)$request->get('product');
        $productInformation = $doctrine->getRepository(Products::class)->find($product->productId);
        $session = $this->sessionService->checkSession();
        $cart = new Cart();

        $resposta = json_decode($this->getQuantityCart($doctrine, false, $product)->getContent());
        if (!$resposta->status) {
            return new JsonResponse(["status" => false]);
        }

        $orderCart = $this->orderCartService->checkSessionOrderCart($session);

        /**
         * validação se o produto é grid ou não
         */
        $productQuantity = $product->productQuantity;

        if ($productInformation->isGrid()) {
            $colorId = is_null($product->colorId) ? null : $product->colorId;
            $sizeId = is_null($product->sizeId) ? null : $product->sizeId;

            $productGrid = $doctrine->getRepository(ProductsGrid::class)->findOneBy(['product_id' => $productInformation->getId(), 'grid_color_id' => $colorId, 'grid_size_id' => $sizeId]);
//            dd($productGrid);
//            $cart->set($productInformation->getId());
            $cart->setProductId($productInformation->getId());
            //$cart->setProductGridId($productGrid->getId());

        }

        $cart->setProductName($productInformation->getName());
        $cart->setPrice($productInformation->getPrice());
        $cart->setQtd($productQuantity);
        $cart->setProductId($productInformation->getId());
        $cart->setSession($session);
        $cart->setOrderCartId($orderCart['id']);

        $doctrine->getManager()->persist($cart);
        $doctrine->getManager()->flush();

//        $grids = $conn->query("SELECT g.id AS grid_id, g.name AS name, pd.id as product_grid_id FROM products_grid as pd
//                               INNER JOIN grid AS g
//                               ON pd.grid_id = g.id
//                               WHERE pd.product_id = $productInformation->getId() and g.status = 1 and g.type = 0")->fetchAll();

        return new JsonResponse(["status" => true]);
    }

    /**
     * @Route("/get_products_to_cart", name="get_products_to_cart")
     */
    public function getProductsToCart(ManagerRegistry $doctrine, Request $request): Response
    {
        $session = $this->sessionService->checkSession();

        $en = $doctrine->getManager();

        $conn = $en->getConnection();

        $qb = $en->createQueryBuilder();

        $query = $qb->select('a')
            ->from('App\Entity\Cart', 'a')
            ->where("a.session = '$session'")
            ->getQuery();

        $products = $query->getArrayResult();

        $productsArray = [];
        $array = [];


        $total = 0;
        $fee = $doctrine->getRepository(SystemConfig::class)->find(1)->getFixedFee();;
        foreach ($products as $product) {
//            $productObject = new \stdClass();
            $productObject = (object)$product;
            $productId = $product['product_id'];
//            $productInfo = $doctrine->getRepository(Products::Class)->find($product['product_id']);


            $en = $doctrine->getManager();

            $qb2 = $en->createQueryBuilder();

            $query = $qb2->select('p.name ,p.price, p.id, p.code, p.description, p.image, p.stock, p.grid, c.name as categoryName, b.name as brandName, t.name as typeName, bor.name as boreName')
                ->from('App\Entity\Products', 'p')
                ->innerJoin('App\Entity\Categories', 'c', 'WITH', "p.category_id = c.id")
                ->leftJoin('App\Entity\Brand', 'b', 'WITH', "b.id = p.brand_id")
                ->leftJoin('App\Entity\Type', 't', 'WITH', "t.id = p.type_id")
                ->leftJoin('App\Entity\Bore', 'bor', 'WITH', "bor.id = p.bore_id")
                ->where("p.id = $productId")
                ->getQuery();

            $productInfo = $query->getArrayResult()[0];

            $qbTags = $en->createQueryBuilder();

            $queryTags = $qbTags->select('t.name')
                ->from('App\Entity\ProductsTags', 'pt')
                ->innerJoin('App\Entity\Products', 'p', 'WITH', "pt.product_id = p.id")
                ->innerJoin('App\Entity\Tags', 't', 'WITH', "t.id = pt.tag_id")
                ->where("p.id = $productId")
                ->getQuery();
//        dd($qbTags);
            $tags = $queryTags->getArrayResult();

            $productObject->tags = $tags;
            $productObject->image = $productInfo['image'];
            $productObject->brandName = $productInfo['brandName'];
            $productObject->categoryName = $productInfo['categoryName'];
            $productObject->typeName = $productInfo['typeName'];
            $productObject->boreName = $productInfo['boreName'];
            if ($productInfo['grid'] == 1) {
                //$productObject->stock = $this->productsService->productGridStockSum($productInfo['id'])['stock'];
                $productObject->stock = 999999;
            } else {
                //productObject->stock = $productInfo['stock'];
                $productObject->stock = 999999;
            }
            $productObject->totalCart = $this->productsService->getQuantitydCart($productInfo['id'], $doctrine);
            //dd($productObject);
            if (!is_null($productObject->product_grid_id)) {
                $productGridId = $doctrine->getRepository(ProductsGrid::Class)->find($product['product_grid_id'])->getId();

                $grids = $conn->query("SELECT g.name AS name_color, g2.name AS name_size, g.id AS color_id, g2.id AS size_id, pg.id AS product_grid_id, pg.code FROM products_grid AS pg left JOIN grid AS g ON pg.grid_color_id = g.id LEFT JOIN grid AS g2 on pg.grid_size_id = g2.id WHERE pg.id = $productGridId ORDER by g.type, g.name;")->fetch();
                $productObject->productColor = $grids['name_color'];
                $productObject->productSize = $grids['name_size'];

                $productObject->image = $productInfo['image'];

            }

            $total += number_format($productObject->price, 2) * $productObject->qtd;
            $productsArray[] = $productObject;
        }

        $orderCart = $this->orderCartService->checkSessionOrderCart($session);

        if (!is_null($orderCart['voucher_id'])) {
            $voucher = $this->vouchersService->checkVoucherById($orderCart['voucher_id']);
            if ($voucher['active']) {
                $voucherDiscount = new \stdClass();
                $voucherDiscount->id = $voucher['id'];
                $voucherDiscount->voucher = $voucher['name'];
                $voucherDiscount->voucherPercentage = $voucher['percentage'];

                $voucherValue = $total * ($voucher['percentage'] / 100);
                $voucherDiscount->voucherDiscountValue = $voucherValue;
                $voucherDiscount->orderCartId = $orderCart['id'];

                $array['voucher'] = $voucherDiscount;
            } else {
                $this->vouchersService->removeVoucher($orderCart['voucher_id']);
            }
        }

        $totalProducts = $total;

        $total += $fee;
        $array['products'] = $productsArray;
        $array['totalProducts'] = number_format($totalProducts, 2);
        $array['total'] = number_format((float)$total, 2);
        $array['fee'] = number_format($fee, 2);
        return new JsonResponse($array);
    }

    /**
     * @Route("/get-total-cart", name="get-total-cart")
     */
    public function getTotalCart(ManagerRegistry $doctrine, Request $request): Response
    {
        $session = $this->sessionService->checkSession();

        $products = $doctrine->getRepository(Cart::class)->findBy(['session' => $session]);

        $en = $doctrine->getManager();

        $conn = $en->getConnection();

        $qb = $en->createQueryBuilder();

        $query = $qb->select('a')
            ->from('App\Entity\Cart', 'a')
            ->where("a.session = '$session'")
            ->getQuery();

        $products = $query->getArrayResult();

        $productsArray = [];
        $array = [];

        $total = 0;
        foreach ($products as $product) {
//            $productObject = new \stdClass();
            $productObject = (object)$product;
            $productInfo = $doctrine->getRepository(Products::Class)->find($product['product_id']);
            $productObject->image = $productInfo->getImage();

            if (!is_null($productObject->product_grid_id)) {
                $productGridId = $doctrine->getRepository(ProductsGrid::Class)->find($product['product_grid_id'])->getId();

                $grids = $conn->query("SELECT g.name AS name_color, g2.name AS name_size, g.id AS color_id, g2.id AS size_id, pg.id AS product_grid_id, pg.code FROM products_grid AS pg left JOIN grid AS g ON pg.grid_color_id = g.id LEFT JOIN grid AS g2 on pg.grid_size_id = g2.id WHERE pg.id = $productGridId ORDER by g.type, g.name;")->fetch();
                $productObject->productColor = $grids['name_color'];
                $productObject->productSize = $grids['name_size'];

                $productObject->image = $productInfo->getImage();

            }

            $total += number_format($productObject->price, 2) * number_format($productObject->qtd, 2);
            $productsArray[] = $productObject;
        }
        $array['products'] = $productsArray;
        $array['total'] = number_format($total, 2);
//        dd($productsArray);
        return new JsonResponse($array);
    }

    /**
     * @Route("/add_product_to_favorite/{productId}", name="add_product_to_favorite")
     */
    public function add_product_to_favorite(ManagerRegistry $doctrine, Request $request): Response
    {
        $en = $doctrine->getManager();
        $productId = $request->get('productId');
        $product = $en->getRepository(FavoriteProducts::class)->find($productId);
        //dd($product);
        if (!is_null($product)) {
            return new Response();
        }
        if (!$product) {
            // Se o produto não existe, cria um novo objeto FavoriteProducts
            $product = new FavoriteProducts();
        }


        $user = $this->guestContactService->getUserSession();
        if (is_null($user)) {
            return new JsonResponse(['login' => true]);
        }

        $product->setProductsId($productId);
        $product->setUserId($user->getId());
        $en->persist($product);
        $en->flush();

        return new Response();
    }

    /**
     * @Route("/get_favorite_products", name="get_favorite_products")
     */
    public function getFavoriteProducts(ManagerRegistry $doctrine): Response
    {
        $en = $doctrine->getManager();

        $conn = $en->getConnection();

        $user = $this->guestContactService->getUserSession();
        $userId = $user->getId();
        $favoriteProducts = $conn->query("select p.id, p.image, p.name as description, p.price, pf.id as id_favorite from products p, favorite_products pf where p.id = pf.products_id and pf.user_id = $userId")->fetchAll();
        return new JsonResponse($favoriteProducts);
    }

    /**
     * @Route("/delete_favorite_product", name="delete_favorite_product")
     */
    public function deleteFavoriteProduct(ManagerRegistry $doctrine, Request $request): Response
    {
        //dd($request->get('productId'));
        $product = $doctrine->getRepository(FavoriteProducts::class)->findOneBy(['products_id' => $request->get('productId')]);
        //dd($product);
        $doctrine->getManager()->remove($product);
        $doctrine->getManager()->flush($product);
        return new Response();
    }

    /**
     * @Route("/check_grid_stock", name="check_grid_stock")
     */
    public function checkGridStock(Request $request)
    {
        $colorId = $request->get('colorId');
        $sizeId = $request->get('sizeId');
        $productId = $request->get('productId');
        $response = new \stdClass();
        $response = $this->productsService->checkGridStock($colorId, $sizeId, $productId);

        return new JsonResponse($response);
    }

    /**
     * @Route("admin/sync_all_products_grids_stock", name="admin/sync_all_products_grids_stock")
     */
    public function syncAllProductsGrids(Request $request)
    {
        $response = $this->productsService->syncProductGridStockIds($request->get('productId'));
        return new JsonResponse($response);
    }

    /**
     * @Route("admin/sync_products_stock", name="admin/sync_products_stock")
     */
    public function syncProduct(Request $request)
    {
        $response = $this->productsService->syncProductsStock($request->get('productId'));
        return new JsonResponse($response);
    }

    /**
     * @Route("/get_quantity_cart", name="get_quantity_cart")
     */
    public function getQuantityCart(ManagerRegistry $doctrine, $pagamento = true, $productInformation = null): Response
    {
        $session = $this->sessionService->checkSession();
        $products = $doctrine->getRepository(Cart::class)->findBy(['session' => $session]);
        if (is_null($productInformation) && $pagamento) {
            foreach ($products as $product) {
                $quantityCart = $this->productsService->getQuantitydCart($product->getProductId(), $doctrine);

                if (is_null($product->getProductGridId())) {
                    //$quantityStock = $this->productsService->getQuantityStock($product->getProductId(), $doctrine);
                    $quantityStock = 999999;
                } else {
                    //$quantityStock = ($this->productsService->productGridStockSum($product->getProductId()))['stock'];
                    $quantityStock = 999999;
                }
                if ($quantityStock < $quantityCart) {
                    return new JsonResponse(["status" => false]);
                }
            }
        } else {
            foreach ($products as $product) {
                if ($product->getProductId() == $productInformation->productId) {
                    $quantityCart = $this->productsService->getQuantitydCart($product->getProductId(), $doctrine);

                    if (is_null($product->getProductGridId())) {
                        //$quantityStock = $this->productsService->getQuantityStock($product->getProductId(), $doctrine);
                        $quantityStock = 999999;
                    } else {
                        //$quantityStock = ($this->productsService->productGridStockSum($product->getProductId()))['stock'];
                        $quantityStock = 999999;
                    }
                    if ($pagamento) {
                        if ($quantityStock < $quantityCart) {
                            return new JsonResponse(["status" => false]);
                        }
                    } else {
                        if ($quantityStock < ($quantityCart + $productInformation->productQuantity)) {
                            return new JsonResponse(["status" => false]);
                        }
                    }
                }

            }
        }
        return new JsonResponse(["status" => true]);
    }
}
