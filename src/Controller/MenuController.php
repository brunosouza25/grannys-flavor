<?php

namespace App\Controller;

use App\Entity\Products;
use App\Entity\Foodmenuitemsmultiple;
use App\Entity\Images;
use App\Entity\Categories;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MenuController extends AbstractController
{
    /**
     * @Route("/menu", name="app_menu")
     */
    public function index(ManagerRegistry $doctrine): Response
    {
        $en = $doctrine->getManager();

        $qb = $en->createQueryBuilder();

        $query = $qb->select('a')
            ->from('App\Entity\Categories', 'a')
            ->where('a.timeinm <= :timeinm')
            ->andWhere('a.timeoutm >= :timeoutm')
            ->orWhere("a.timeinm = '' AND a.timeoutm = ''")
            ->andWhere('a.menustate = :menustate')
            ->setParameter('menustate', 1)
            ->setParameter('timeinm', date('H:i'))
            ->setParameter('timeoutm', date('H:i'))
            ->getQuery();

        $categories = $query->getResult();
        $qb2 = $en->createQueryBuilder();

        $query = $qb2->select('a')
            ->from('App\Entity\Products', 'a')
            ->where('a.timeinm <= :timeinm')
            ->andWhere('a.timeoutm >= :timeoutm')
            ->orWhere("a.timeinm = '' AND a.timeoutm = ''")
            ->andWhere('a.menustate = :menustate')
            ->setParameter('menustate', 1)
            ->setParameter('timeinm', date('H:i'))
            ->setParameter('timeoutm', date('H:i'))
            ->getQuery();
        $items = $query->getResult();

        $multipeitems = $doctrine->getRepository(Foodmenuitemsmultiple::class)->findAll();
        $banner = $doctrine->getRepository(Images::class)->findOneBy(['type' => 'banner2']);

        return $this->render('menu/index.html.twig', [
            'titlePage' => 'Menu',
            'categories' => $categories,
            'items' => $items,
            'multipleitems' => $multipeitems,
            'banner' => $banner
        ]);
    }
}
