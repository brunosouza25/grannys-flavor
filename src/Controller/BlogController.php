<?php

namespace App\Controller;

use App\Entity\Blog;
use App\Entity\Images;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    /**
     * @Route("/noticias", name="app_blog")
     */
    public function index(ManagerRegistry $doctrine): Response
    {
        $blogs = $doctrine->getRepository(Blog::class)->findAll();
        $banner = $doctrine->getRepository(Images::class)->findOneBy(['type' => 'banner2']);

        return $this->render('blog/index.html.twig', [
            'titlePage' => 'Blog',
            'blogs' => $blogs,
            'banner' => $banner
        ]);
    }

    /**
     * @Route("/noticia/{referencekey}", name="app_blog_detail")
     */
    public function blogDetail(ManagerRegistry $doctrine, Request $request): Response
    {
        $reference = $request->get('referencekey');

        $blog = $doctrine->getRepository(Blog::class)->findOneBy(array('reference' => $reference));

        return $this->render('blog/detail.html.twig', [
            'titlePage' => 'Blog',
            'blog' => $blog
        ]);
    }
}
