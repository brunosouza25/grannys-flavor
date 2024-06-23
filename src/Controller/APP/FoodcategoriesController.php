<?php

namespace App\Controller\APP;

use App\Entity\Products;
use App\Entity\Categories;
use App\Entity\Sliderscategory;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/** @Route("/app", name="app") */

class FoodcategoriesController extends AbstractController
{
    /**
     * @Route("/foodcategories", name="/app_foodcategories")
     */
    public function index(ManagerRegistry $doctrine): Response
    {

        $categories = $doctrine->getRepository(Categories::class)->findBy(array('state' => '1'));
        $items = $doctrine->getRepository(Products::class)->findAll();
        $getsliders = $doctrine->getRepository(Sliderscategory::class)->findAll();

        return $this->render('APP/foodcategories/index.html.twig', [
            'titlePage' => 'Categorias',
            'categories' => $categories,
            'items' => $items,
            'sliders' => $getsliders

        ]);
    }
}
