<?php

namespace App\Controller;

use App\Entity\Gallery;
use App\Entity\Images;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontGalleryController extends AbstractController
{
    /**
     * @Route("/gallery", name="app_front_gallery")
     */
    public function index(ManagerRegistry $doctrine): Response
    {

        $images = $doctrine->getRepository(Gallery::class)->findAll();
        $banner = $doctrine->getRepository(Images::class)->findOneBy(['type' => 'banner2']);


        return $this->render('front_gallery/index.html.twig', [
            'titlePage' => 'Galeria',
            'images' => $images,
            'banner' => $banner,
        ]);
    }
}
