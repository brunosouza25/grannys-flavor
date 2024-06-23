<?php

namespace App\Controller\ADMIN;

use App\Entity\Blog;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/** @Route("/admin", name="admin/") */

class BlogsController extends AbstractController
{
    /**
     * @Route("/blogs", name="app_blogs")
     */
    public function index(ManagerRegistry $doctrine): Response
    {
        $blogs = $doctrine->getRepository(Blog::class)->findAll();

        return $this->render('ADMIN/blogs/index.html.twig', [
            'titlePage' => 'Blog',
            'blogs' => $blogs
        ]);
    }

    /**
     * @Route("/blogs/add-new", name="app_blogs_add_new")
     */
    public function addNewBlog(): Response
    {
        return $this->render('ADMIN/blogs/addblog.html.twig', [
            'titlePage' => 'Adicionar Blog',
        ]);
    }

    /**
     * @Route("/blogs/save-new", name="app_blogs_save_new")
     */
    public function saveNewBlog(ManagerRegistry $doctrine, Request $request): Response
    {
        $ref = $request->get('menu-ref');
        $title = $request->get('menu-title');


        if(isset($_FILES['imageblog']['name'])) {
            $filename = $_FILES['imageblog']['name'];

            $location = "uploads/blog/" . $filename;

            $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
            $imageFileType = strtolower($imageFileType);

            $valid_extensions = array("jpg", "jpeg", "png");

            if (in_array(strtolower($imageFileType), $valid_extensions)) {

                $path = "uploads/blog/".time(). "-" .$_FILES['imageblog']['name'];
                copy($_FILES['imageblog']['tmp_name'], $path);

            }
        }

        $text = $request->get('textblog');
        $date = date('d-m-Y');
        $addBlog = new Blog();
        $addBlog->setTitle($title);
        $addBlog->setReference($ref);
        $addBlog->setImage($path);
        $addBlog->setDate($date);
        $addBlog->setText($text);
        $doctrine->getManager()->persist($addBlog);
        $doctrine->getManager()->flush();
        return new Response();
    }
}
