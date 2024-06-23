<?php

namespace App\Controller\ADMIN;

use App\Entity\Families;
use App\Entity\Subfamilie;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/** @Route("/admin", name="admin/") */
class FamiliesController extends AbstractController
{
    /**
     * @Route("/families", name="app_families")
     */
    public function index(ManagerRegistry $doctrine): Response
    {
        $en = $doctrine->getManager();

        $listFamilies = $doctrine->getRepository(Families::class)->findAll();


        return $this->render('ADMIN/families/index.html.twig', [
            'titlePage' => 'Lista de Familias',
            'families' => $listFamilies
        ]);
    }

    /**
     * @Route("/sub-families", name="app_sub_families")
     */
    public function indexSubFamilies(ManagerRegistry $doctrine): Response
    {
        $en = $doctrine->getManager();

        $listSubfamilie = $doctrine->getRepository(Subfamilie::class)->findAll();
        $listFamilies = $doctrine->getRepository(Families::class)->findAll();


        return $this->render('families/subfamilies.html.twig', [
            'titlePage' => 'Lista de Sub Familias',
            'subfamilies' => $listSubfamilie,
            'families' => $listFamilies
        ]);
    }

//    /**
//     * @Route("/options-groups", name="app_options_groups")
//     */
//    public function optionsGroup(ManagerRegistry $doctrine): Response
//    {
//        $en = $doctrine->getManager();
//
//        $listSubfamilie = $doctrine->getRepository(Subfamilie::class)->findAll();
//        $listFamilies = $doctrine->getRepository(Families::class)->findAll();
//
//
//        return $this->render('families/subfamilies.html.twig', [
//            'titlePage' => 'Lista de Sub Familias',
//            'subfamilies' => $listSubfamilie,
//            'families' => $listFamilies
//        ]);
//    }

    /**
     * @Route("/familie-state-change", name="app_familie_state_change")
     */
    public function familieStateChange(ManagerRegistry $doctrine, Request $request): Response
    {

        $en = $doctrine->getManager();

        $productID = $request->get('productID');

        $product = $doctrine->getRepository(Families::class)->find($productID);

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
     * @Route("/sub-familie-state-change", name="app_sub_familie_state_change")
     */
    public function subFamilieStateChange(ManagerRegistry $doctrine, Request $request): Response
    {

        $en = $doctrine->getManager();

        $productID = $request->get('productID');

        $product = $doctrine->getRepository(Subfamilie::class)->find($productID);

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
}
