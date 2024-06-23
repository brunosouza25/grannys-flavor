<?php

namespace App\Controller;

use App\Entity\Models;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ModelsController extends AbstractController
{
    /**
     * @Route("/admin/models", name="admin/models")
     */
    public function index(): Response
    {
        return $this->render('models/index.html.twig', [
            'controller_name' => 'ModelsController',
        ]);
    }

    /**
     * @Route("/admin/get_models", name="admin/get_models")
     */
    public function getModels(ManagerRegistry $doctrine): Response
    {
        $en = $doctrine->getManager();

        $qb = $en->createQueryBuilder();

        $query = $qb->select('a')
            ->from('App\Entity\Models', 'a')
            ->where('a.status = 1')
            ->getQuery();

        $models = $query->getArrayResult();

        return new JsonResponse($models);

    }

    /**
     * @Route("/admin/new_model", name="admin/new_model")
     */
    public function newModel(ManagerRegistry $doctrine, Request $request): Response
    {
        $en = $doctrine->getManager();

        $model = new Models();
        $model->setName($request->get('itemName'));
        $model->setStatus(true);

        $en->persist($model);
        $en->flush();

        return new Response();
    }

    /**
     * @Route("/admin/edit_model", name="admin/edit_model")
     */
    public function editModel(ManagerRegistry $doctrine, Request $request): Response
    {
        $modelId = $request->get('itemId');
        $modelName = $request->get('itemName');

        $en = $doctrine->getManager();

        $model = $doctrine->getRepository(Models::class)->find($modelId);
        $model->setName($modelName);

        $en->persist($model);
        $en->flush();

        return new Response();
    }

    /**
     * @Route("/admin/delete_model", name="admin/delete_model")
     */
    public function deleteModel(ManagerRegistry $doctrine, Request $request): Response
    {
        $modelId = $request->get('itemId');

        $en = $doctrine->getManager();
        $model = $doctrine->getRepository(Models::class)->find($modelId);
        $model->setStatus(0);
        $en->persist($model);
        $en->flush();

        return new Response();
    }


    /**
     * @Route("/admin/get_model", name="admin/get_model")
     */
    public function getModel(ManagerRegistry $doctrine, Request $request): Response
    {

        $en = $doctrine->getManager();

        $qb = $en->createQueryBuilder();
        $query = $qb->select('a')
            ->from('App\Entity\Models', 'a')
            ->where('a.id = :id')
            ->setParameter('id', $request->get('modelId'))
            ->getQuery();

        $model = $query->getArrayResult();

        return new JsonResponse($model[0]);

    }
}
