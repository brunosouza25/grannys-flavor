<?php

namespace App\Controller;

use App\Entity\Brand;
use App\Entity\Equipament;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EquipamentsController extends AbstractController
{
    /**
     * @Route("/admin/equipaments", name="admin/equipaments")
     */
    public function index(): Response
    {
        return $this->render('equipaments/index.html.twig', [
            'titlePage' => 'EquipamentsController',
        ]);
    }

    /**
     * @Route("/admin/get_equipaments", name="admin/get_equipaments")
     */
    public function getEquipaments(ManagerRegistry $doctrine): Response
    {
        $en = $doctrine->getManager();

        $qb = $en->createQueryBuilder();

        $query = $qb->select('a')
            ->from('App\Entity\Equipament', 'a')
            ->where('a.status = 1')
            ->orderBy('a.name')
            ->getQuery();

        $brands = $query->getArrayResult();

        return new JsonResponse($brands);

    }

    /**
     * @Route("/admin/new_equipament", name="admin/new_equipament")
     */
    public function newEquipament(ManagerRegistry $doctrine, Request $request): Response
    {
        $en = $doctrine->getManager();

        $equipament = new Equipament();
        $equipament->setName($request->get('itemName'));
        $equipament->setStatus(true);

        $en->persist($equipament);
        $en->flush();

        return new Response();
    }

    /**
     * @Route("/admin/edit_equipament", name="/admin/edit_equipament")
     */
    public function editEquipament(ManagerRegistry $doctrine, Request $request): Response
    {
        $equipamentId = $request->get('itemId');
        $equipamentName = $request->get('itemName');

        $en = $doctrine->getManager();

        $equipament = $doctrine->getRepository(Equipament::class)->find($equipamentId);
        $equipament->setName($equipamentName);

        $en->persist($equipament);
        $en->flush();

        return new Response();
    }

    /**
     * @Route("/admin/delete_equipament", name="admin/delete_equipament")
     */
    public function deleteEquipament(ManagerRegistry $doctrine, Request $request): Response
    {
        $equipamentId = $request->get('itemId');

        $en = $doctrine->getManager();
        $equipament = $doctrine->getRepository(Equipament::class)->find($equipamentId);
        $equipament->setStatus(0);
        $en->persist($equipament);
        $en->flush();

        return new Response();
    }


    /**
     * @Route("/admin/get_equipament", name="admin/get_equipament")
     */
    public function getEquipament(ManagerRegistry $doctrine, Request $request): Response
    {

        $en = $doctrine->getManager();

        $qb = $en->createQueryBuilder();
        $query = $qb->select('a')
            ->from('App\Entity\Equipament', 'a')
            ->where('a.id = :id')
            ->setParameter('id', $request->get('equipamentId'))
            ->getQuery();

        $equipament = $query->getArrayResult();

        return new JsonResponse($equipament[0]);

    }
}
