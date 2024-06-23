<?php

namespace App\Controller\APP;

use App\Entity\Appcart;
use App\Entity\Appcartextras;
use App\Entity\Foodadicionalcategory;
use App\Entity\Foodadicionalconnectionitemmultiple;
use App\Entity\Foodadicionalitems;
use App\Entity\Products;
use App\Entity\Foodmenuitemsmultiple;
use App\Service\SessionService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/** @Route("/app", name="app") */

class ProductDetailController extends AbstractController
{
    /**
     * @Route("/product/detail/{reference}", name="/app_product_detail")
     */
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {

        $productID = $request->get('reference');

        $getitemData = $doctrine->getRepository(Products::class)->find($productID);

        $typeitem = $getitemData->getType();
        $multipleconnections = $doctrine->getRepository(Foodadicionalconnectionitemmultiple::class)->findAll();
        $adicionalTitles = $doctrine->getRepository(Foodadicionalcategory::class)->findAll();
        $adicionalItems = $doctrine->getRepository(Foodadicionalitems::class)->findAll();

        $minvalue = array();

        if($typeitem == 'multiple'){

            $getmultipleitem = $doctrine->getRepository(Foodmenuitemsmultiple::class)->findBy(array('idfooditem' => $productID));
            $sizes = $doctrine->getRepository(Foodmenuitemsmultiple::class)->findBy(array('idfooditem' => $productID));
            foreach ($getmultipleitem as $valueitem){
                $minvalue[] = $valueitem->getItemprice();
            }
            $minprice = min($minvalue);




        }else{
            $sizes = $doctrine->getRepository(Foodmenuitemsmultiple::class)->findAll();
            $minprice = $getitemData->getPrice();
            $itemsMultiple = 0;
        }


//dd($multipleconnections);


        return $this->render('APP/product_detail/index.html.twig', [
            'titlePage' => 'Produto',
            'item' => $getitemData,
            'minprice' => $minprice,
            'sizes' => $sizes,
            'multipleconections' => $multipleconnections,
            'adicionalCategory' => $adicionalTitles,
            'adicionalItems' => $adicionalItems
        ]);
    }


    /**
     * @Route("/add-to-cart-item", name="/app_add_to_cart_item")
     */
    public function addtoCartItem(Request $request, ManagerRegistry $doctrine): Response
    {
        $itemID = $request->get('itemsize');
        $extras = $request->get('extras');
        $commnent = $request->get('comment-item');
        $qtd = $request->get('qtd');
        $getItemSizeData = $doctrine->getRepository(Foodmenuitemsmultiple::class)->find($itemID);
        $getitemData = $doctrine->getRepository(Products::class)->find($getItemSizeData->getIdfooditem());


        $foodadicionalitems = $doctrine->getRepository(Foodadicionalitems::class)->findBy(array('id' => $extras));

        $extrasPrices = 0;

        foreach ($foodadicionalitems as $price){
            $extrasPrices = $extrasPrices + $price->getPrice();
        }

        $total = ($extrasPrices + $getItemSizeData->getItemprice())*$qtd;

        $en = $doctrine->getManager();

        $cart = new Appcart();
        $cart->setIduser($this->getUser()->getId());
        $cart->setItem($getitemData->getName());
        $cart->setQtd($qtd);
        $cart->setPrice($total);
        $cart->setImage($getitemData->getImage());
        $cart->setType($getitemData->getType());
        $cart->setSizeid($itemID);
        $cart->setComment($commnent);
        $en->persist($cart);
        $en->flush();

        foreach ($foodadicionalitems as $value){
            $cartextras = new Appcartextras();
            $cartextras->setOrderid($cart->getId());
            $cartextras->setExtraid($value->getId());
            $en->persist($cartextras);
        }
        $en->flush();

        return new JsonResponse();
    }


}
