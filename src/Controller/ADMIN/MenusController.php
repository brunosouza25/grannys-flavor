<?php

namespace App\Controller\ADMIN;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/** @Route("/admin", name="admin/") */
class MenusController extends AbstractController
{
    /**
     * @Route("/page-menus", name="app_page_menus")
     */
    public function index(): Response
    {
        return $this->render('ADMIN/menus/index.html.twig', [
            'titlePage' => 'Menus',
        ]);
    }
}
