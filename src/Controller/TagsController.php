<?php

namespace App\Controller;

use App\Entity\Tags;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TagsController extends AbstractController
{
    /**
     * @Route("/admin/tags", name="admin/tags")
     */
    public function index(): Response
    {
        return $this->render('tags/index.html.twig', [
            'controller_name' => 'TagsController',
        ]);
    }

    /**
     * @Route("/admin/get_tags", name="admin/get_tags")
     */
    public function getTags(ManagerRegistry $doctrine): Response
    {
        $en = $doctrine->getManager();

        $qb = $en->createQueryBuilder();

        $query = $qb->select('a')
            ->from('App\Entity\Tags', 'a')
            ->where('a.status = 1')
            ->orderBy('a.name')
            ->getQuery();

        $tags = $query->getArrayResult();

        return new JsonResponse($tags);

    }

    /**
     * @Route("/admin/new_tag", name="admin/new_tag")
     */
    public function newTag
    (ManagerRegistry $doctrine, Request $request): Response
    {
        $en = $doctrine->getManager();

        $tag = new Tags();
        $tag->setName($request->get('itemName'));
        $tag->setStatus(true);

        $en->persist($tag);
        $en->flush();

        return new Response();
    }

    /**
     * @Route("/admin/edit_tag", name="admin/edit_tag")
     */
    public function editTag
    (ManagerRegistry $doctrine, Request $request): Response
    {
        $tagId = $request->get('itemId');
        $tagName = $request->get('itemName');

        $en = $doctrine->getManager();

        $tag = $doctrine->getRepository(Tags::class)->find($tagId);
        $tag->setName($tagName);

        $en->persist($tag);
        $en->flush();

        return new Response();
    }

    /**
     * @Route("/admin/delete_tag", name="admin/delete_tag")
     */
    public function deleteTag
    (ManagerRegistry $doctrine, Request $request): Response
    {
        $tagId = $request->get('itemId');

        $en = $doctrine->getManager();
        $tag = $doctrine->getRepository(Tags::class)->find($tagId);
        $tag->setStatus(0);
        $en->persist($tag);
        $en->flush();

        return new Response();
    }


    /**
     * @Route("/admin/get_tag", name="admin/get_tag")
     */
    public function getTag
    (ManagerRegistry $doctrine, Request $request): Response
    {

        $en = $doctrine->getManager();

        $qb = $en->createQueryBuilder();
        $query = $qb->select('a')
            ->from('App\Entity\Tags', 'a')
            ->where('a.id = :id')
            ->setParameter('id', $request->get('tagId'))
            ->getQuery();

        $tag = $query->getArrayResult();

        return new JsonResponse($tag[0]);

    }
}
