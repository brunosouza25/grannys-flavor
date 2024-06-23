<?php

namespace App\Controller;

use App\Entity\Type;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TypesController extends AbstractController
{
    /**
     * @Route("/admin/types", name="admin/types")
     */
    public function index(): Response
    {
        return $this->render('types/index.html.twig', [
            'controller_name' => 'TypesController',
        ]);
    }
    /**
     * @Route("/admin/get_types", name="admin/get_types")
     */
    public function getTypes(ManagerRegistry $doctrine): Response
    {
        $en = $doctrine->getManager();

        $qb = $en->createQueryBuilder();

        $query = $qb->select('a')
            ->from('App\Entity\Type', 'a')
            ->where('a.status = 1')
            ->orderBy('a.name')
            ->getQuery();

        $types = $query->getArrayResult();

        return new JsonResponse($types);

    }

    /**
     * @Route("/admin/new_type", name="admin/new_type")
     */
    public function newType(ManagerRegistry $doctrine, Request $request): Response
    {
        $en = $doctrine->getManager();

        $type = new Type();
        $type->setName($request->get('itemName'));
        $type->setStatus(true);

        $en->persist($type);
        $en->flush();

        return new Response();
    }

    /**
     * @Route("/admin/edit_type", name="admin/edit_type")
     */
    public function editType(ManagerRegistry $doctrine, Request $request): Response
    {
        $typeId = $request->get('itemId');
        $typeName = $request->get('itemName');

        $en = $doctrine->getManager();

        $type = $doctrine->getRepository(Type::class)->find($typeId);
        $type->setName($typeName);

        $en->persist($type);
        $en->flush();

        return new Response();
    }

    /**
     * @Route("/admin/delete_type", name="admin/delete_type")
     */
    public function deleteType(ManagerRegistry $doctrine, Request $request): Response
    {
        $typeId = $request->get('itemId');

        $en = $doctrine->getManager();
        $type = $doctrine->getRepository(Type::class)->find($typeId);
        $type->setStatus(0);
        $en->persist($type);
        $en->flush();

        return new Response();
    }


    /**
     * @Route("/admin/get_type", name="admin/get_type")
     */
    public function getType(ManagerRegistry $doctrine, Request $request): Response
    {

        $en = $doctrine->getManager();

        $qb = $en->createQueryBuilder();
        $query = $qb->select('a')
            ->from('App\Entity\Type', 'a')
            ->where('a.id = :id')
            ->setParameter('id', $request->get('typeId'))
            ->getQuery();

        $type = $query->getArrayResult();

        return new JsonResponse($type[0]);

    }
}
