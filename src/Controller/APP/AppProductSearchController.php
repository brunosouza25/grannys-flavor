<?php

namespace App\Controller\APP;

use App\Entity\Products;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/** @Route("/app", name="app") */

class AppProductSearchController extends AbstractController
{
    /**
     * @Route("/app/product/search", name="app_product_search")
     */
    public function index(ManagerRegistry $doctrine): Response
    {

        $getallProducts = $doctrine->getRepository(Products::class)->findBy(array('state' => '1'));



        return $this->render('APP/app_product_search/index.html.twig', [
            'titlePage' => 'Pesquisar Produto',
            'products' => $getallProducts
        ]);
    }
}
