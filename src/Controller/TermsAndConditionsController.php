<?php

namespace App\Controller;

use App\Entity\Images;
use App\Entity\Ourteam;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TermsAndConditionsController extends AbstractController
{
    /**
     * @Route("/termsconditions", name="termsconditions")
     */
    public function index(ManagerRegistry $doctrine): Response
    {
        return $this->render('details/termsAndConditions.html.twig', [
            'titlePage' => 'Termos e Condições',
        ]);
    }

    /**
     * @Route("/privacypolicy", name="privacypolicy")
     */
    public function privacyPolicy(ManagerRegistry $doctrine): Response
    {
        return $this->render('termsandcondition.html.twig', [
            'titlePage' => 'Política de Privacidade',
        ]);
    }
}
