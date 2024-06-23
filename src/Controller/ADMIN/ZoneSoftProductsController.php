<?php

namespace App\Controller\ADMIN;

use App\Entity\Families;
use App\Entity\ProductsZoneSoft;
use App\Entity\Subfamilie;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/** @Route("/admin", name="admin/") */
class ZoneSoftProductsController extends AbstractController

{
    /**
     * @Route("/products_zonesoft", name="app_products_zonesoft")
     */
    public function index(ManagerRegistry $doctrine): Response
    {

        $en = $doctrine->getManager();

        $listProducts = $doctrine->getRepository(ProductsZoneSoft::class)->findAll();

        return $this->render('ADMIN/products/index.html.twig', [
            'titlePage' => 'Lista de Produtos',
            'products' => $listProducts
        ]);
    }

    /**
     * @Route("/zonesoft-products-detail/{reference}", name="app_zonesoft_products_detail")
     */
    public function productDetail(ManagerRegistry $doctrine, Request $request): Response
    {

        $en = $doctrine->getManager();

        $productID = $request->get('reference');

        $product = $doctrine->getRepository(ProductsZoneSoft::class)->find($productID);



        $familie = $doctrine->getRepository(Families::class)->findOneBy(array('codigo' => $product->getFimilyid()));

        $subfamilie = $doctrine->getRepository(Subfamilie::class)->findOneBy(array('idfamily'=> $product->getFimilyid()));

        $familieN = $familie->getDescricao();
        $subfamilieN = $subfamilie->getDescricao();

        return $this->render('ADMIN/products/product-detail.html.twig', [
            'titlePage' => $product->getDescricao(),
            'product' => $product,
            'familie' => $familieN,
            'subfamilie' => $subfamilieN
        ]);
    }


    /**
     * @Route("/products-state-change-zonesoft", name="app_products_state_change-zonesoft")
     */
    public function productsStateChange(ManagerRegistry $doctrine, Request $request): Response
    {

        $en = $doctrine->getManager();

        $productID = $request->get('productID');

        $product = $doctrine->getRepository(ProductsZoneSoft::class)->find($productID);

        $productState = $product->getState();

        if($productState == 1){
            $product->setState(0);
            $en->persist($product);
            $en->flush();
        }else{
            $product->setState(1);
            $en->persist($product);
            $en->flush();
        }

      return new Response();
    }

    /**
     * @Route("/products-upload-image-zonesoft", name="app_products_upload_image-zonesoft")
     */
    public function productsUploadImage(ManagerRegistry $doctrine, Request $request): Response
    {

        if(isset($_FILES) && !empty($_FILES)) {
            $remove_products_ids = array();
            if(isset($_POST['remove_products_ids']) && !empty($_POST['remove_products_ids'])) {
                $remove_products_ids = explode(",", $_POST['remove_products_ids']);
            }
            for($i=0; $i<sizeof($_FILES['products_uploaded']['name']); $i++) {
                if(!in_array($i, $remove_products_ids)) {
                    if($_FILES['products_uploaded']['name'][$i] != "") {
                        $path = "uploads/".time(). "-" .$_FILES['products_uploaded']['name'][$i];
                        copy($_FILES['products_uploaded']['tmp_name'][$i], $path);
                        $id = $request->get('productID');
                        $en = $doctrine->getManager();
                        $product = $doctrine->getRepository(ProductsZoneSoft::class)->find($id);
                        $product->setFoto($path);
                        $en->persist($product);
                        $en->flush();
                    }
                }
            }
        }

        return new Response();
    }

}
