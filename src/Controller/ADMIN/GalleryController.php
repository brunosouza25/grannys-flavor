<?php

namespace App\Controller\ADMIN;

use App\Entity\Gallery;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/** @Route("/admin", name="admin/") */
class GalleryController extends AbstractController
{
    /**
     * @Route("/gallery", name="app_gallery")
     */
    public function index(ManagerRegistry $doctrine): Response
    {
        $images = $doctrine->getRepository(Gallery::class)->findAll();

        return $this->render('ADMIN/gallery/index.html.twig', [
            'titlePage' => 'Galeria',
            'images' => $images
        ]);
    }

    /**
     * @Route("/gallery/upload-new-images", name="app_gallery_new_images")
     */
    public function UploadNewImages(ManagerRegistry $doctrine): Response
    {
        if(isset($_FILES) && !empty($_FILES)) {
            $remove_products_ids = array();
            if(isset($_POST['remove_products_ids']) && !empty($_POST['remove_products_ids'])) {
                $remove_products_ids = explode(",", $_POST['remove_products_ids']);
            }
            for($i=0; $i<sizeof($_FILES['products_uploaded']['name']); $i++) {
                if(!in_array($i, $remove_products_ids)) {
                    if($_FILES['products_uploaded']['name'][$i] != "") {
                        $path = "uploads/gallery/".time(). "-" .$_FILES['products_uploaded']['name'][$i];
                        copy($_FILES['products_uploaded']['tmp_name'][$i], $path);

                       $en = $doctrine->getManager();
                        $uploadImgs = new Gallery();
                        $uploadImgs->setImage($path);
                        $uploadImgs->setTitle('FabiosRoadStop');
                        $en->persist($uploadImgs);
                        $en->flush();
                    }
                }
            }
        }


        return new Response();
    }

    /**
     * @Route("/gallery/delete-new-images", name="app_gallery_delete_images")
     */
    public function DeleteNewImages(ManagerRegistry $doctrine, Request $request): Response
    {
        $imageID = $request->get('imageID');
        $getimage = $doctrine->getRepository(Gallery::class)->find($imageID);
        $doctrine->getManager()->remove($getimage);
        $doctrine->getManager()->flush();

        return new Response();
    }
}
