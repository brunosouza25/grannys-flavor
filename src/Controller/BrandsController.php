<?php

namespace App\Controller;

use App\Entity\Brand;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BrandsController extends AbstractController
{
    /**
     * @Route("/admin/brands", name="admin/brands")
     */
    public function index(): Response
    {
        return $this->render('brands/index.html.twig', [
            'titlePage' => 'BrandsController',
        ]);
    }

    /**
     * @Route("/admin/get_brands", name="admin/get_brands")
     */
    public function getBrands(ManagerRegistry $doctrine): Response
    {
        $en = $doctrine->getManager();

        $qb = $en->createQueryBuilder();

        $query = $qb->select('a')
            ->from('App\Entity\Brand', 'a')
            ->where('a.status = 1')
            ->orderBy('a.name')
            ->getQuery();

        $brands = $query->getArrayResult();

        return new JsonResponse($brands);

    }

    /**
     * @Route("/admin/new_brand", name="admin/new_brand")
     */
    public function newBrand(ManagerRegistry $doctrine, Request $request): Response
    {
        $en = $doctrine->getManager();

        $brand = new Brand();
        $brand->setName($request->get('itemName'));
        $brand->setStatus(true);

        $en->persist($brand);
        $en->flush();

        return new Response();
    }

    /**
     * @Route("/admin/edit_brand", name="admin/edit_brand")
     */
    public function editBrand(ManagerRegistry $doctrine, Request $request): Response
    {
        $brandId = $request->get('itemId');
        $brandName = $request->get('itemName');

        $en = $doctrine->getManager();

        $brand = $doctrine->getRepository(Brand::class)->find($brandId);
        $brand->setName($brandName);

        $en->persist($brand);
        $en->flush();

        return new Response();
    }

    /**
     * @Route("/admin/delete_brand", name="admin/delete_brand")
     */
    public function deleteBrand(ManagerRegistry $doctrine, Request $request): Response
    {
        $brandId = $request->get('itemId');

        $en = $doctrine->getManager();
        $brand = $doctrine->getRepository(Brand::class)->find($brandId);
        $brand->setStatus(0);
        $en->persist($brand);
        $en->flush();

        return new Response();
    }


    /**
     * @Route("/admin/get_brand", name="admin/get_brand")
     */
    public function getBrand(ManagerRegistry $doctrine, Request $request): Response
    {

        $en = $doctrine->getManager();

        $qb = $en->createQueryBuilder();
        $query = $qb->select('a')
            ->from('App\Entity\Brand', 'a')
            ->where('a.id = :id')
            ->setParameter('id', $request->get('brandId'))
            ->getQuery();

        $brand = $query->getArrayResult();

        return new JsonResponse($brand[0]);

    }
}
