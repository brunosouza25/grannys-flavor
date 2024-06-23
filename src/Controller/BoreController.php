<?php

namespace App\Controller;

use App\Entity\Bore;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BoreController extends AbstractController
{
    /**
     * @Route("/admin/bores", name="admin/bores")
     */
    public function index(): Response
    {
        return $this->render('bore/index.html.twig', [
            'controller_name' => 'BoreController',
        ]);
    }

    /**
     * @Route("/admin/get_bores", name="admin/get_bores")
     */
    public function getBores(ManagerRegistry $doctrine): Response
    {
        $en = $doctrine->getManager();

        $qb = $en->createQueryBuilder();

        $query = $qb->select('a')
            ->from('App\Entity\Bore', 'a')
            ->where('a.status = 1')
            ->orderBy('a.name')
            ->getQuery();

        $bores = $query->getArrayResult();

        return new JsonResponse($bores);

    }

    /**
     * @Route("/admin/new_bore", name="admin/new_bore")
     */
    public function newBore(ManagerRegistry $doctrine, Request $request): Response
    {
        $en = $doctrine->getManager();

        $bore = new Bore();
        $bore->setName($request->get('itemName'));
        $bore->setStatus(true);

        $en->persist($bore);
        $en->flush();

        return new Response();
    }

    /**
     * @Route("/admin/edit_bore", name="admin/edit_bore")
     */
    public function editBore(ManagerRegistry $doctrine, Request $request): Response
    {
        $boreId = $request->get('itemId');
        $boreName = $request->get('itemName');

        $en = $doctrine->getManager();

        $bore = $doctrine->getRepository(Bore::class)->find($boreId);
        $bore->setName($boreName);

        $en->persist($bore);
        $en->flush();

        return new Response();
    }

    /**
     * @Route("/admin/delete_bore", name="admin/delete_bore")
     */
    public function deleteBore(ManagerRegistry $doctrine, Request $request): Response
    {
        $boreId = $request->get('itemId');

        $en = $doctrine->getManager();
        $bore = $doctrine->getRepository(Bore::class)->find($boreId);
        $bore->setStatus(0);
        $en->persist($bore);
        $en->flush();

        return new Response();
    }


    /**
     * @Route("/admin/get_bore", name="admin/get_bore")
     */
    public function getBore(ManagerRegistry $doctrine, Request $request): Response
    {

        $en = $doctrine->getManager();

        $qb = $en->createQueryBuilder();
        $query = $qb->select('a')
            ->from('App\Entity\Bore', 'a')
            ->where('a.id = :id')
            ->setParameter('id', $request->get('boreId'))
            ->getQuery();

        $bore = $query->getArrayResult();

        return new JsonResponse($bore[0]);

    }
}
