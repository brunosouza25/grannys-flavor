<?php

namespace App\Controller;

use App\Entity\Material;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MaterialsController extends AbstractController
{
    /**
     * @Route("/admin/materials", name="admin/materials")
     */
    public function index(): Response
    {
        return $this->render('materials/index.html.twig', [
            'controller_name' => 'MaterialsController',
        ]);
    }

    /**
     * @Route("/admin/get_materials", name="admin/get_materials")
     */
    public function getMaterials(ManagerRegistry $doctrine): Response
    {
        $en = $doctrine->getManager();

        $qb = $en->createQueryBuilder();

        $query = $qb->select('a')
            ->from('App\Entity\Material', 'a')
            ->where('a.status = 1')
            ->getQuery();

        $materials = $query->getArrayResult();

        return new JsonResponse($materials);

    }

    /**
     * @Route("/admin/new_material", name="admin/new_material")
     */
    public function newMaterial(ManagerRegistry $doctrine, Request $request): Response
    {
        $en = $doctrine->getManager();

        $material = new Material();
        $material->setName($request->get('itemName'));
        $material->setStatus(true);

        $en->persist($material);
        $en->flush();

        return new Response();
    }

    /**
     * @Route("/admin/edit_material", name="admin/edit_material")
     */
    public function editMaterial(ManagerRegistry $doctrine, Request $request): Response
    {
        $materialId = $request->get('itemId');
        $materialName = $request->get('itemName');

        $en = $doctrine->getManager();

        $material = $doctrine->getRepository(Material::class)->find($materialId);
        $material->setName($materialName);

        $en->persist($material);
        $en->flush();

        return new Response();
    }

    /**
     * @Route("/admin/delete_material", name="admin/delete_material")
     */
    public function deleteMaterial(ManagerRegistry $doctrine, Request $request): Response
    {
        $materialId = $request->get('itemId');

        $en = $doctrine->getManager();
        $material = $doctrine->getRepository(Material::class)->find($materialId);
        $material->setStatus(0);
        $en->persist($material);
        $en->flush();

        return new Response();
    }


    /**
     * @Route("/admin/get_material", name="admin/get_material")
     */
    public function getMaterial(ManagerRegistry $doctrine, Request $request): Response
    {

        $en = $doctrine->getManager();

        $qb = $en->createQueryBuilder();
        $query = $qb->select('a')
            ->from('App\Entity\Material', 'a')
            ->where('a.id = :id')
            ->setParameter('id', $request->get('materialId'))
            ->getQuery();

        $material = $query->getArrayResult();

        return new JsonResponse($material[0]);

    }
}
