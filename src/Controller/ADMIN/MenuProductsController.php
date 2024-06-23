<?php

namespace App\Controller\ADMIN;

use App\Entity\Foodadicionalcategory;
use App\Entity\Foodadicionalconnection;
use App\Entity\Foodadicionalconnectionintem;
use App\Entity\Foodadicionalconnectionitemmultiple;
use App\Entity\Foodadicionalitems;
use App\Entity\Products;
use App\Entity\Foodmenuitemsmultiple;
use App\Entity\Categories;
use App\Entity\ProductsZoneSoft;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/** @Route("/admin", name="admin/") */
class MenuProductsController extends AbstractController
{
    /**
     * @Route("/menu", name="app_menu_products")
     */
    public function index(ManagerRegistry $doctrine): Response
    {
        $en = $doctrine->getManager();

        $qb = $en->createQueryBuilder('p');
        $qb->select('c')
            ->from('App\Entity\Categories', 'c')
            ->orderBy('c.ordernr', 'ASC');

        $categories = $qb->getQuery()->getResult();

        $qbitems = $en->createQueryBuilder('i');
        $qbitems->select('i')
            ->from('App\Entity\Products', 'i')
            ->orderBy('i.ordernr', 'ASC');

        $items = $qbitems->getQuery()->getResult();


        $qbitemsm = $en->createQueryBuilder('m');
        $qbitemsm->select('m')
            ->from('App\Entity\Foodmenuitemsmultiple', 'm')
            ->orderBy('m.id', 'ASC');

        $itemsm = $qbitemsm->getQuery()->getResult();



        $qbadccat = $en->createQueryBuilder('c');
        $qbadccat->select('c')
            ->from('App\Entity\Foodadicionalcategory', 'c')
            ->orderBy('c.id', 'ASC');

        $adicionalcat = $qbadccat->getQuery()->getResult();


        $qbadconn = $en->createQueryBuilder('a');
        $qbadconn->select('a')
            ->from('App\Entity\Foodadicionalconnection', 'a')
            ->orderBy('a.id', 'ASC');

        $adicionalconn = $qbadconn->getQuery()->getResult();

        $qbadconni = $en->createQueryBuilder('it');
        $qbadconni->select('it')
            ->from('App\Entity\Foodadicionalconnectionintem', 'it')
            ->orderBy('it.id', 'ASC');

        $adicionalconni = $qbadconni->getQuery()->getResult();

        $qbadconnim = $en->createQueryBuilder('itm');
        $qbadconnim->select('itm')
            ->from('App\Entity\Foodadicionalconnectionitemmultiple', 'itm')
            ->orderBy('itm.id', 'ASC');

        $adicionalconnim = $qbadconnim->getQuery()->getResult();

        $qbadconnitm = $en->createQueryBuilder('adcitm');
        $qbadconnitm->select('adcitm')
            ->from('App\Entity\Foodadicionalitems', 'adcitm')
            ->orderBy('adcitm.id', 'ASC');

        $adicionalitm = $qbadconnitm->getQuery()->getResult();

        $listProducts = $doctrine->getRepository(ProductsZoneSoft::class)->findAll();



        return $this->render('ADMIN/menu_products/index.html.twig', [
            'titlePage' => 'Produtos e Acompanhamentos',
            'categories' => $categories,
            'items' => $items,
            'itemsm' => $itemsm,
            'adicional' => $adicionalcat,
            'connectionsadd' => $adicionalconn,
            'connectitemadd' => $adicionalconni,
            'connectitemaddm' => $adicionalconnim,
            'adicionalitm' => $adicionalitm,
            'zoneProducts' => $listProducts
        ]);
    }

    /**
     * @Route("/menu/products/add-category", name="app_menu_products_add_categoty")
     */
    public function menuAddCategory(Request $request, ManagerRegistry $doctrine): Response
    {
        $menuShow = $request->get('menu-show');
        $timein = $request->get('timein');
        $timeout = $request->get('timeout');
        $orderingShow = $request->get('orderingShow');
        $timeinm = $request->get('timeinm');
        $timeoutm = $request->get('timeoutm');

        if($orderingShow == 'on'){
            $orderingShow = 1;
        }else{
            $orderingShow = 0;
        }
        if($menuShow == 'on'){
            $menuShow = 1;
        }else{
            $menuShow = 0;
        }

        $categoryname = $request->get('category-name');
        $categorydesc = $request->get('category-desc');

        $en = $doctrine->getManager();

        $addcategory = new Categories();
        $addcategory->setName($categoryname);
        $addcategory->setDescription($categorydesc);
        $addcategory->setTimein($timein);
        $addcategory->setTimeout($timeout);
        $addcategory->setTimeinm($timeinm);
        $addcategory->setTimeoutm($timeoutm);
        $addcategory->setMenustate($menuShow);
        $addcategory->setState($orderingShow);
        $addcategory->setLevel(0);
        $addcategory->setOrdernr(9999);

        if(isset($_FILES['file']['name'])) {
            $filename = $_FILES['file']['name'];

            $location = "uploads/" . $filename;

            $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
            $imageFileType = strtolower($imageFileType);

            $valid_extensions = array("jpg", "jpeg", "png");

            if (in_array(strtolower($imageFileType), $valid_extensions)) {
                $path = "uploads/".time(). "-" .$_FILES['file']['name'];
                copy($_FILES['file']['tmp_name'], $path);
                $addcategory->setImage($path);
            }
        }

        $en->persist($addcategory);
        $en->flush();
        return  new Response();
    }

    /**
     * @Route("/menu/products/remove-category", name="app_menu_products_remove_category")
     */
    public function menuRemoveCategory(Request $request, ManagerRegistry $doctrine): Response
    {

        $en = $doctrine->getManager();

        $category = $en->getRepository(Categories::class)->find($request->get('id'));

        $products = $en->getRepository(Products::class)->findBy(['idcategory' => $request->get('id')]);

        foreach ($products as $product) {
            if ($product->getType() == 'multiple') {
                $productsMultiple = $en->getRepository(Foodmenuitemsmultiple::class)->findBy(['idfooditem' => $product->getId()]);

                foreach ($productsMultiple as $productMultiple) {
                    $en->remove($productMultiple);

                }
            }
            $en->remove($product);
        }

        $en->remove($category);

        $en->flush();

        return  new Response();
    }


    /**
     * @Route("/menu/products/change-positions-category", name="app_menu_products_change_positions_category")
     */
    public function menuChangePositionsCategory(Request $request, ManagerRegistry $doctrine): Response
    {

        $position = $request->get('position');
        $categoryId = $request->get('categoryid');

        $en = $doctrine->getManager();
        $categorydata = $doctrine->getRepository(Categories::class)->find($categoryId);

        $categorydata->setOrdernr($position);

        $en->persist($categorydata);
        $en->flush();

        return  new Response();
    }


    /**
     * @Route("/menu/products/add-image-category", name="app_menu_products_add_imagecategory")
     */
    public function menuAddImageCategory(Request $request, ManagerRegistry $doctrine): Response
    {
        if(isset($_FILES['file']['name'])) {
            $filename = $_FILES['file']['name'];

            $location = "uploads/" . $filename;

            $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
            $imageFileType = strtolower($imageFileType);

            $valid_extensions = array("jpg", "jpeg", "png");

            if (in_array(strtolower($imageFileType), $valid_extensions)) {

                $path = "uploads/".time(). "-" .$_FILES['file']['name'];
                copy($_FILES['file']['tmp_name'], $path);
                $id = $request->get('categoryid');
                $en = $doctrine->getManager();
                $product = $doctrine->getRepository(Categories::class)->find($id);
                $product->setImage($path);
                $en->persist($product);
                $en->flush();
            }
        }

        return  new Response();
    }

    /**
     * @Route("/menu/products/add-image-item-image", name="app_menu_products_add_item_image")
     */
    public function menuAddImageitem(Request $request, ManagerRegistry $doctrine): Response
    {
        if(isset($_FILES['file']['name'])) {
            $filename = $_FILES['file']['name'];

            $location = "uploads/" . $filename;

            $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
            $imageFileType = strtolower($imageFileType);

            $valid_extensions = array("jpg", "jpeg", "png");

            if (in_array(strtolower($imageFileType), $valid_extensions)) {

                $path = "uploads/".time(). "-" .$_FILES['file']['name'];
                copy($_FILES['file']['tmp_name'], $path);
                $id = $request->get('categoryid');
                $en = $doctrine->getManager();
                $product = $doctrine->getRepository(Products::class)->find($id);
                $product->setImage($path);
                $en->persist($product);
                $en->flush();
            }
        }

        return  new Response();
    }

    /**
     * @Route("/menu/products/add-image-category-update", name="app_menu_products_add_imagecategory_update")
     */
    public function menuAddImageCategoryUpdate(Request $request, ManagerRegistry $doctrine): Response
    {
        if(isset($_FILES['file']['name'])) {
            $filename = $_FILES['file']['name'];
            $location = "uploads/" . $filename;

            $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
            $imageFileType = strtolower($imageFileType);

            $valid_extensions = array("jpg", "jpeg", "png");

            if (in_array(strtolower($imageFileType), $valid_extensions)) {

                $path = "uploads/".time(). "-" .$_FILES['file']['name'];
                $id = $request->get('categoryid');
                $en = $doctrine->getManager();
                $product = $doctrine->getRepository(Categories::class)->find($id);
                $currenctimg = $product->getImage();

                if(file_exists($path)){
                unlink($currenctimg);
                }

                copy($_FILES['file']['tmp_name'], $path);
                $product->setImage($path);
                $en->persist($product);
                $en->flush();
            }
        }

        return  new Response();
    }


    /**
     * @Route("/menu/products/add-image-item-update", name="app_menu_products_add_imageitem_update")
     */
    public function menuAddImageitemUpdate(Request $request, ManagerRegistry $doctrine): Response
    {
        if(isset($_FILES['file']['name'])) {
            $filename = $_FILES['file']['name'];
            $location = "uploads/" . $filename;

            $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
            $imageFileType = strtolower($imageFileType);

            $valid_extensions = array("jpg", "jpeg", "png");

            if (in_array(strtolower($imageFileType), $valid_extensions)) {



                $src = $_FILES['file']['tmp_name'];
                $dest =  "uploads/".explode('.',$_FILES['file']['name'])[0].'.webp';
                $destjpeg =  "uploads/".time(). "-" .$_FILES['file']['name'];

                $quality = 40;

                    $info = getimagesize($src);

                    if ($info['mime'] == 'image/jpeg')
                    {
                        $image = imagecreatefromjpeg($src);
                    }
                    elseif ($info['mime'] == 'image/gif')
                    {
                        $image = imagecreatefromgif($src);
                    }
                    elseif ($info['mime'] == 'image/png')
                    {
                        $image = imagecreatefrompng($src);
                    }
                    else
                    {
                        die('Unknown image file format');
                    }

                    //compress and save file to jpg
                    imagejpeg($image, $destjpeg, $quality);

                    // Create and save
                    $img = imagecreatefromjpeg($destjpeg);
                    imagepalettetotruecolor($img);
                    imagealphablending($img, true);
                    imagesavealpha($img, true);
                    imagewebp($img,$dest, 10);
                    imagedestroy($img);

                    unlink($destjpeg);
                    //return destination file


                $id = $request->get('categoryid');
                $en = $doctrine->getManager();
                $product = $doctrine->getRepository(Products::class)->find($id);
                $currenctimg = $product->getImage();

                if(file_exists($dest)){
                    unlink($currenctimg);
                }

//                copy($_FILES['file']['tmp_name'], $dest);

                $product->setImage($dest);
                $en->persist($product);
                $en->flush();
            }
        }


        return  new Response();
    }



    /**
     * @Route("/menu/products/add-image-category-delete", name="app_menu_products_add_imagecategory_delete")
     */
    public function menuAddImageCategoryDelete(Request $request, ManagerRegistry $doctrine): Response
    {

        $id = $request->get('categoryid');
        $en = $doctrine->getManager();
        $category = $doctrine->getRepository(Categories::class)->find($id);
        $currenctimg = $category->getImage();
        unlink($currenctimg);

        $category->setImage(null);
        $en->persist($category);
        $en->flush();


        return  new Response();
    }

    /**
     * @Route("/menu/products/add-item", name="app_menu_products_add_item")
     */
    public function menuAdditem(Request $request, ManagerRegistry $doctrine): Response
    {
        $menuShow = $request->get('menu-show');
        $timein = $request->get('timein');
        $timeout = $request->get('timeout');
        $orderingShow = $request->get('orderingShow');
        $timeinm = $request->get('timeinm');
        $timeoutm = $request->get('timeoutm');

        if($orderingShow == 'on'){
            $orderingShow = 1;
        }else{
            $orderingShow = 0;
        }
        if($menuShow == 'on'){
            $menuShow = 1;
        }else{
            $menuShow = 0;
        }



        $en = $doctrine->getManager();

        $itemname = $request->get('item-name');
        $itempricesingle = $request->get('item-price-single');
        $itemdescription = $request->get('item-description');
        $typeitem = $request->get('typeitem');
        $categoryId = $request->get('categoryId');

        $itemnamemultiple = $request->get('item-name-multiple'); // come in array
        $itempricesinglemultiple = $request->get('item-price-single-multiple'); // come in array

        $newItem = new Products();
        $newItem->setState($orderingShow);
        $newItem->setMenustate($menuShow);
        $newItem->setTimein($timein);
        $newItem->setTimeout($timeout);
        $newItem->setTimeinm($timeinm);
        $newItem->setTimeoutm($timeoutm);
        $newItem->setCategoryId($categoryId);
        $newItem->setName($itemname);
        $newItem->setPrice($itempricesingle);
        $newItem->setDescription($itemdescription);
        $newItem->setType($typeitem);

        if(isset($_FILES['file']['name'])) {
            $filename = $_FILES['file']['name'];

            $location = "uploads/" . $filename;

            $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
            $imageFileType = strtolower($imageFileType);

            $valid_extensions = array("jpg", "jpeg", "png");

            if (in_array(strtolower($imageFileType), $valid_extensions)) {
                $path = "uploads/".time(). "-" .$_FILES['file']['name'];
                copy($_FILES['file']['tmp_name'], $path);
                $newItem->setImage($path);
            }
        }

        $newItem->setOrdernr(999);
        $en->persist($newItem);
        $en->flush();

        if($typeitem == 'multiple'){
            $arraymultipe = array_combine($itemnamemultiple, $itempricesinglemultiple);
            foreach ($arraymultipe as $key => $value){
            $newitemMultiple = new Foodmenuitemsmultiple();
            $newitemMultiple->setIdcategory($categoryId);
            $newitemMultiple->setIdfooditem($newItem->getId());
            $newitemMultiple->setItemname($key);
            $newitemMultiple->setItemprice($value);
            $en->persist($newitemMultiple);
        }

        $en->flush();
        }

        return  new Response();
    }

    /**
     * @Route("/menu/products/remove-product", name="app_menu_products_remove_it")
     */
    public function menuRemoveProduct(Request $request, ManagerRegistry $doctrine): Response
    {
        $en = $doctrine->getManager();

        $itemCategory = $en->getRepository(Products::class)->find($request->get('id'));
//        dd($itemCategory);
        $en->remove($itemCategory);
        $en->flush();

        return  new Response();
    }

    /**
     * @Route("/menu/products/edit-item", name="app_menu_products_edit_item")
     */
    public function menuEdititem(Request $request, ManagerRegistry $doctrine): Response
    {
        $menuShow = $request->get('menu-show');
        $timein = $request->get('timein');
        $timeout = $request->get('timeout');
        $orderingShow = $request->get('orderingShow');
        $timeinm = $request->get('timeinm');
        $timeoutm = $request->get('timeoutm');

        if($orderingShow == 'on'){
            $orderingShow = 1;
        }else{
            $orderingShow = 0;
        }
        if($menuShow == 'on'){
            $menuShow = 1;
        }else{
            $menuShow = 0;
        }

        $en = $doctrine->getManager();

        $itemname = $request->get('item-name');
        $itempricesingle = $request->get('item-price-single');
        $itemdescription = $request->get('item-desc');
        $typeitem = $request->get('typeItem');
        $categoryId = $request->get('categoryId');
        $itemID = $request->get('itemID');
        $zonesoftcode = $request->get('zonesoftcode');


        $itemnamemultiple = $request->get('item-name-multiple'); // come in array
        $itempricesinglemultiple = $request->get('item-price-single-multiple'); // come in array

        if($typeitem == 'multiple'){

            $getmultipleData = $doctrine->getRepository(Foodmenuitemsmultiple::class)->findBy(array('idfooditem' => $itemID));


            foreach ($getmultipleData as $mtvalue){
                $en->remove($mtvalue);
                $en->flush();
            }

            $newArray = [];

//            for ($i = 0; $i <= count($itemnamemultiple); $i++){
//                $products = new Products();
//                $newitemMultiple = new Foodmenuitemsmultiple();
//                $newitemMultiple->setIdcategory($categoryId);
//                $newitemMultiple->setIdfooditem($itemID);
//                $newitemMultiple->setItemname($key);
//                $newitemMultiple->setItemprice($value);
//                $newitemMultiple->setZonesoftcode($value);
//            }


            $arraymultipe = array_combine($itemnamemultiple, $itempricesinglemultiple);

            foreach ($arraymultipe as $key => $value){
                $zsCode = empty($zonesoftcode[array_search($key, array_keys($arraymultipe))]) ? 1 : $zonesoftcode[array_search($key, array_keys($arraymultipe))];

                $newitemMultiple = new Foodmenuitemsmultiple();
                $newitemMultiple->setIdcategory($categoryId);
                $newitemMultiple->setIdfooditem($itemID);
                $newitemMultiple->setItemname($key);
                $newitemMultiple->setItemprice($value);
                $newitemMultiple->setZonesoftcode($zsCode);

                $en->persist($newitemMultiple);
                $en->flush();

            }
            $zonesoftcode = '';
        }

        $getItemData = $doctrine->getRepository(Products::class)->find($itemID);
        $getItemData->setName($itemname);
        $getItemData->setDescription($itemdescription);
        $getItemData->setPrice($itempricesingle);
        $getItemData->setZonesoftcode($zonesoftcode);
        $getItemData->setState($orderingShow);
        $getItemData->setMenustate($menuShow);
        $getItemData->setTimein($timein);
        $getItemData->setTimeout($timeout);
        $getItemData->setTimeinm($timeinm);
        $getItemData->setTimeoutm($timeoutm);

        if(isset($_FILES['file']['name'])) {
            $filename = $_FILES['file']['name'];

            $location = "uploads/" . $filename;

            $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
            $imageFileType = strtolower($imageFileType);

            $valid_extensions = array("jpg", "jpeg", "png");

            if (in_array(strtolower($imageFileType), $valid_extensions)) {
                $path = "uploads/".time(). "-" .$_FILES['file']['name'];
                copy($_FILES['file']['tmp_name'], $path);
                $getItemData->setImage($path);
            }
        }

        $en->persist($getItemData);
        $en->flush();


        return  new Response();
    }


    /**
     * @Route("/menu/products/add-adicional-category", name="app_menu_products_add_adicional_category")
     */
    public function menuAddAdicionalCategory(Request $request, ManagerRegistry $doctrine): Response
    {

        $name = $request->get('category-name-adicional');
        $type = $request->get('type-adicional');
        $multiple = $request->get('multiple-times-adicional');

        if($multiple == 'on'){
            $multiple = '1';
        }else{
            $multiple = '0';
        }

        $min = $request->get('min-val-adicional');
        $max = $request->get('max-val-adicional');

        $en = $doctrine->getManager();

        $adicional = new Foodadicionalcategory();
        $adicional->setName($name);
        $adicional->setType($type);
        $adicional->setMultiple($multiple);
        $adicional->setMin($min);
        $adicional->setMax($max);

        $en->persist($adicional);
        $en->flush();


        return  new Response();
    }

    /**
     * @Route("/menu/products/edit-adicional-category", name="app_menu_products_edit_adicional_category")
     */
    public function menuEditAdicionalCategory(Request $request, ManagerRegistry $doctrine): Response
    {

        $name = $request->get('category-name-adicional');
        $type = $request->get('type-adicional');
        $multiple = $request->get('multiple-times-adicional');
        $idADCategory = $request->get('idAdicionalForm');

        if($multiple == 'on'){
            $multiple = '1';
        }else{
            $multiple = '0';
        }

        $min = $request->get('min-val-adicional');
        $max = $request->get('max-val-adicional');

        $en = $doctrine->getManager();

        $adicional = $doctrine->getRepository(Foodadicionalcategory::class)->find($idADCategory);


        $adicional->setName($name);
        $adicional->setType($type);
        $adicional->setMultiple($multiple);
        $adicional->setMin($min);
        $adicional->setMax($max);

        $en->persist($adicional);
        $en->flush();


        return  new Response();
    }

    /**
     * @Route("/menu/products/app_menu_products_edit_single_multiple", name="app_menu_products_edit_single_multiple")
     * Change product from Single to Multiple
     */
    public function menuEditSingleMultiple(Request $request, ManagerRegistry $doctrine): Response
    {
        $productId = $request->get('itemID');

        $en = $doctrine->getManager();

        $product = $doctrine->getRepository(Products::class)->find($productId);

        $product->setType('multiple');

        $en->persist($product);
        $en->flush();

        return  new Response();
    }


    /**
     * @Route("/menu/products/edit-category-data", name="app_menu_products_edit_category_data")
     */
    public function editCategoryData(Request $request, ManagerRegistry $doctrine): Response
    {
        $menuShow = $request->get('menu-show');
        $timein = $request->get('timein');
        $timeout = $request->get('timeout');
        $orderingShow = $request->get('orderingShow');
        $timeinm = $request->get('timeinm');
        $timeoutm = $request->get('timeoutm');
        $name = $request->get('category-name');
        $desc = $request->get('category-desc');
        $catID = $request->get('category-id');

        if($orderingShow == 'on'){
            $orderingShow = 1;
        }else{
            $orderingShow = 0;
        }
        if($menuShow == 'on'){
            $menuShow = 1;
        }else{
            $menuShow = 0;
        }

        $en = $doctrine->getManager();

        $getCategoryData = $doctrine->getRepository(Categories::class)->find($catID);

        $getCategoryData->setName($name);
        $getCategoryData->setDescription($desc);
        $getCategoryData->setState($orderingShow);
        $getCategoryData->setMenustate($menuShow);
        $getCategoryData->setTimein($timein);
        $getCategoryData->setTimeout($timeout);
        $getCategoryData->setTimeinm($timeinm);
        $getCategoryData->setTimeoutm($timeoutm);

        $en->persist($getCategoryData);
        $en->flush();

        return  new Response();
    }

    /**
     * @Route("/menu/products/add-adicional-item", name="app_menu_products_add_adicional_item")
     */
    public function menuAddAdicionalItem(Request $request, ManagerRegistry $doctrine): Response
    {

        $en = $doctrine->getManager();

        $nameAdicional = $request->get('name-adicional-item');
        $priceAdicional = $request->get('price-adicional-item');
        $idadicional = $request->get('idadicional');
        $preselected = $request->get('preselected');
        $zoneSoftCode = $request->get('zonesoftcode');

        if($preselected == 'on'){
            $preselected = '1';
        }else{
            $preselected = '0';
        }
        $newAdicional = new Foodadicionalitems();
        $newAdicional->setName($nameAdicional);
        $newAdicional->setPrice($priceAdicional);
        $newAdicional->setIdadicional($idadicional);
        $newAdicional->setPreselected($preselected);
        $newAdicional->setIdZoneSoft($zoneSoftCode);
        $en->persist($newAdicional);
        $en->flush();

        return  new Response();
    }

   /**
     * @Route("/menu/products/delete-adicional-item", name="app_menu_products_delete_adicional_item")
     */
    public function menuDeleteAdicionalItem(Request $request, ManagerRegistry $doctrine): Response
    {

        $en = $doctrine->getManager();

        $idAdicional = $request->get('idAdicional');

        $newAdicional = $en->getRepository(Foodadicionalitems::class)->find($idAdicional);
        $en->remove($newAdicional);
        $en->flush();

        return  new Response();
    }

   /**
     * @Route("/menu/products/delete-adicional", name="app_menu_products_delete_adicional")
     */
    public function menuDeleteAdicional(Request $request, ManagerRegistry $doctrine): Response
    {

        $en = $doctrine->getManager();

        $idAdicional = $request->get('idAdicional');

        $adicional = $en->getRepository(Foodadicionalcategory::class)->find($idAdicional);
        $adicionalItems = $en->getRepository(Foodadicionalitems::class)->findBy(['idadicional' => $adicional]);
        foreach ($adicionalItems as $adicionalItem) {
            $en->remove($adicionalItem);

        }

        $en->remove($adicional);

        $en->flush();

        return  new Response();
    }

    /**
     * @Route("/menu/products/edit-adicional-item", name="app_menu_products_edit_adicional_item")
     */
    public function menuEditAdicionalItem(Request $request, ManagerRegistry $doctrine): Response
    {

        $en = $doctrine->getManager();

        $nameAdicional = $request->get('name-adicional-item');
        $priceAdicional = $request->get('price-adicional-item');
        $idadicional = $request->get('idadicional');
        $zoneSoftCode = $request->get('zonesoftcode');

        $getAdicionalItem = $doctrine->getRepository(Foodadicionalitems::class)->find($idadicional);

        $getAdicionalItem->setName($nameAdicional);
        $getAdicionalItem->setPrice($priceAdicional);
        $getAdicionalItem->setIdZoneSoft($zoneSoftCode);
        $en->persist($getAdicionalItem);
        $en->flush();

        return  new Response();
    }


    /**
     * @Route("/menu/products/add-adicional-category-connection", name="app_menu_products_add_adicional_category_connection")
     */
    public function menuAddAdicionalCategoryConnection(Request $request, ManagerRegistry $doctrine): Response
    {
        $categoryid = $request->get('categoryid');
        $adicionalId = $request->get('draggableId');

        $en = $doctrine->getManager();


        $checkexist = $doctrine->getRepository(Foodadicionalconnection::class)->findOneBy(array('idadicional' => $adicionalId, 'idcategoryfood' => $categoryid));

        if ($checkexist == null){
            $connectionAdicional = new Foodadicionalconnection();
            $connectionAdicional->setIdcategoryfood($categoryid);
            $connectionAdicional->setIdadicional($adicionalId);
            $en->persist($connectionAdicional);
            $en->flush();
        }else{
            print_r('1');
        }
        return  new Response();
    }

    /**
     * @Route("/menu/products/add-adicional-item-connection", name="app_menu_products_add_adicional_item_connection")
     */
    public function menuAddAdicionalItemConnection(Request $request, ManagerRegistry $doctrine): Response
    {
        $categoryid = $request->get('categoryid');
        $adicionalId = $request->get('draggableId');
        $itemid = $request->get('itemid');

        $en = $doctrine->getManager();


        $checkexist = $doctrine->getRepository(Foodadicionalconnectionintem::class)->findOneBy(array('idadicional' => $adicionalId, 'idcategoryfood' => $categoryid, 'iditem' => $itemid));
        if ($checkexist == null){
            $connectionAdicional = new Foodadicionalconnectionintem();
            $connectionAdicional->setIdcategoryfood($categoryid);
            $connectionAdicional->setIdadicional($adicionalId);
            $connectionAdicional->setIditem($itemid);
            $en->persist($connectionAdicional);
            $en->flush();
        }else{
            print_r('1');
        }
        return  new Response();
    }

    /**
     * @Route("/menu/products/add-adicional-item-multiple-connection", name="app_menu_products_add_adicional_item_multiple_connection")
     */
    public function menuAddAdicionalItemMultipleConnection(Request $request, ManagerRegistry $doctrine): Response
    {
        $categoryid = $request->get('categoryid');
        $adicionalId = $request->get('draggableId');
        $itemid = $request->get('itemid');
        $itemMultipleId = $request->get('itemMultipleId');

        $en = $doctrine->getManager();


        $checkexist = $doctrine->getRepository(Foodadicionalconnectionitemmultiple::class)->findOneBy(array('idcategory' => $categoryid, 'iditem' => $itemid, 'iditemmultiple' => $itemMultipleId, 'idadicional' => $adicionalId));


        if ($checkexist == null){
            $connectionAdicional = new Foodadicionalconnectionitemmultiple();
            $connectionAdicional->setIdcategory($categoryid);
            $connectionAdicional->setIditem($itemid);
            $connectionAdicional->setIditemmultiple($itemMultipleId);
            $connectionAdicional->setIdadicional($adicionalId);
            $en->persist($connectionAdicional);
            $en->flush();
        }else{
            print_r('1');
        }
        return  new Response();
    }


    /**
     * @Route("/menu/products/delete-adicional-category-connection", name="app_menu_products_delete_adicional_category_connection")
     */
    public function menuDeleteAdicionalCategoryConnection(Request $request, ManagerRegistry $doctrine): Response
    {
        $adicionalID = $request->get('adicionalID');
        $categoryID = $request->get('categoryID');
        $en = $doctrine->getManager();
        $checkexist = $doctrine->getRepository(Foodadicionalconnection::class)->findOneBy(array('idadicional' => $adicionalID, 'idcategoryfood' => $categoryID));
        $en->remove($checkexist);
        $en->flush();
        return  new Response();
    }

    /**
     * @Route("/menu/products/delete-adicional-item-connection", name="app_menu_products_delete_adicional_item_connection")
     */
    public function menuDeleteAdicionalitemConnection(Request $request, ManagerRegistry $doctrine): Response
    {
        $adicionalID = $request->get('adicionalID');
        $categoryID = $request->get('categoryID');
        $itemID = $request->get('itemID');
        $en = $doctrine->getManager();
        $checkexist = $doctrine->getRepository(Foodadicionalconnectionintem::class)->findOneBy(array('idadicional' => $adicionalID, 'idcategoryfood' => $categoryID, 'iditem' => $itemID));
        $en->remove($checkexist);
        $en->flush();
        return  new Response();
    }

    /**
     * @Route("/menu/products/delete-adicional-item-muçtiple-connection", name="app_menu_products_delete_adicional_item_multiple_connection")
     */
    public function menuDeleteAdicionalitemMultipleConnection(Request $request, ManagerRegistry $doctrine): Response
    {
        $adicionalID = $request->get('adicionalID');
        $categoryID = $request->get('categoryID');
        $itemID = $request->get('itemID');
        $itemIDMultiple = $request->get('itemIDMultiple');
        $en = $doctrine->getManager();
        $checkexist = $doctrine->getRepository(Foodadicionalconnectionitemmultiple::class)->findOneBy(array('idcategory' => $categoryID, 'iditem' => $itemID, 'iditemmultiple' => $itemIDMultiple, 'idadicional' => $adicionalID));

        $en->remove($checkexist);
        $en->flush();
        return  new Response();
    }



    /**
     * @Route("/menu/products/get-showing-options", name="app_menu_products_get_showing_options")
     */
    public function menuProductGetShowingOptions(Request $request, ManagerRegistry $doctrine): Response
    {
        $categoryID = $request->get('categoryID');
        $en = $doctrine->getManager();
        $product = $doctrine->getRepository(Categories::class)->find($categoryID);

        return  new JsonResponse([
            'nameshow' => $product->getName(),
            'stateshow' => $product->getState(),
            'menustate' => $product->getMenustate(),
            'timein' => $product->getTimein(),
            'timeout' => $product->getTimeout(),
            'timeinm' => $product->getTimeinm(),
            'timeoutm' => $product->getTimeoutm()
        ]);
    }

    /**
     * @Route("/menu/products/get-showing-products", name="app_menu_products_get_showing")
     */
    public function menuProductGetShowing(Request $request, ManagerRegistry $doctrine): Response
    {
        $productId = $request->get('productId');

        $en = $doctrine->getManager();
        $product = $doctrine->getRepository(Categories::class)->find($productId);

        return  new JsonResponse([
            'nameshow' => $product->getName(),
            'stateshow' => $product->getState(),
            'menustate' => $product->getMenustate(),
            'timein' => $product->getTimein(),
            'timeout' => $product->getTimeout(),
            'timeinm' => $product->getTimeinm(),
            'timeoutm' => $product->getTimeoutm()
        ]);
    }

    /**
     * @Route("/menu/products/get-showing-product", name="get-showing-product")
     */
    public function menuProductShow(Request $request, ManagerRegistry $doctrine): Response
    {
        $productId = $request->get('productId');
        $en = $doctrine->getManager();
        $product = $en->getRepository(Products::class)->find($productId);

        return  new JsonResponse([
            'nameshow' => $product->getName(),
            'stateshow' => $product->getState(),
            'menustate' => $product->getMenustate(),
            'timein' => $product->getTimein(),
            'timeout' => $product->getTimeout(),
            'timeinm' => $product->getTimeinm(),
            'timeoutm' => $product->getTimeoutm()
        ]);
    }

    /**
     * @Route("/menu/products/update-showing-product", name="update-showing-product")
     */
    public function menuProductUpdateShow(Request $request, ManagerRegistry $doctrine): Response
    {
        $menuShow = $request->get('product-MenuShow');
        $timein = $request->get('timein');
        $timeout = $request->get('timeout');
        $orderingShow = $request->get('product-OrderShow');
        $timeinm = $request->get('timeinm');
        $timeoutm = $request->get('timeoutm');
        $productId = $request->get('productId');

        if($orderingShow == 'on'){
            $orderingShow = 1;
        }else{
            $orderingShow = 0;
        }
        if($menuShow == 'on'){
            $menuShow = 1;
        }else{
            $menuShow = 0;
        }

        $en = $doctrine->getManager();
        $product = $doctrine->getRepository(Products::class)->find($productId);

        $product->setState($orderingShow);
        $product->setMenustate($menuShow);
        $product->setTimein($timein);
        $product->setTimeout($timeout);

        $product->setTimeinm($timeinm);
        $product->setTimeoutm($timeoutm);

        $en->persist($product);
        $en->flush();

        return new Response();
    }

    /**
     * @Route("/menu/products/update-showing-options", name="app_menu_products_update_showing_options")
     */
    public function menuProductUpdateShowingOptions(Request $request, ManagerRegistry $doctrine): Response
    {

        $menuShow = $request->get('menu-show');
        $timein = $request->get('timein');
        $timeout = $request->get('timeout');
        $orderingShow = $request->get('orderingShow');
        $timeinm = $request->get('timeinm');
        $timeoutm = $request->get('timeoutm');
        $categoryID = $request->get('categoryID');

        if($orderingShow == 'on'){
            $orderingShow = 1;
        }else{
            $orderingShow = 0;
        }
        if($menuShow == 'on'){
            $menuShow = 1;
        }else{
            $menuShow = 0;
        }

        $en = $doctrine->getManager();
        $product = $doctrine->getRepository(Categories::class)->find($categoryID);

        $product->setState($orderingShow);
        $product->setMenustate($menuShow);
        $product->setTimein($timein);
        $product->setTimeout($timeout);

        $product->setTimeinm($timeinm);
        $product->setTimeoutm($timeoutm);

        $en->persist($product);
        $en->flush();

      return new Response();
    }


}
