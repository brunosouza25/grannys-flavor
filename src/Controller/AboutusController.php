<?php

namespace App\Controller;

use App\Entity\Images;
use App\Entity\Ourteam;
use App\Entity\Texts;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AboutusController extends AbstractController
{
    /**
     * @Route("/sobre-nos", name="app_aboutus")
     */
    public function index(ManagerRegistry $doctrine): Response
    {

        $ourTeam = $doctrine->getRepository(Ourteam::class)->findAll();
        $banner = $doctrine->getRepository(Images::class)->findOneBy(['type' => 'banner2']);
        $images = $doctrine->getRepository(Images::class)->findAll();
        $texts = $doctrine->getRepository(Texts::class)->find(3);

        return $this->render('aboutus/index.html.twig', [
            'titlePage' => 'Sobre Nós',
            'teams' => $ourTeam,
            'banner' => $banner,
            'images' => $images,
            'texts' => $texts

        ]);
    }
}
