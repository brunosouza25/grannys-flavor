<?php

namespace App\Controller\ADMIN;

use App\Entity\Apporders;
use App\Entity\Categories;
use App\Entity\Sliderscategory;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/** @Route("/admin", name="admin") */
class DashboardController extends AbstractController
{
    /**
     * @Route("/", name="/admin-adrea")
     */
    public function adminArea(): Response
    {

        if ($this->getUser()) {
            if($this->getUser()->getRoles()[0] == 'ROLE_USER'){
                return $this->redirectToRoute('admin/app_dashboard');
            }else{
                return $this->redirectToRoute('app/app_dashboard');
            }

        }

        return $this->redirectToRoute("admin/app_login");
    }

    /**
     * @Route("/dashboard", name="/app_dashboard")
     */
    public function index(ManagerRegistry $doctrine): Response
    {

        if ($this->getUser()) {
            if($this->getUser()->getRoles()[0] == 'ROLE_USER'){

            }else{
                return $this->redirectToRoute('app/app_dashboard');
            }

        }
        $en = $doctrine->getManager();

        $conn = $en->getConnection();

        $productsQuantity = $conn->query("SELECT COUNT(id) as quantity FROM products")->fetch()['quantity'];
        $ordersQuantity = $conn->query("SELECT COUNT(id) as quantity FROM orders")->fetch()['quantity'];
        $costumerQuantity = $conn->query("SELECT COUNT(id) as quantity FROM guestcontact")->fetch()['quantity'];
        $totalOrders = $conn->query("SELECT SUM(total) as total FROM order_payments WHERE status = 1;")->fetch()['total'];

        return $this->render('ADMIN/dashboard/index.html.twig', [
            'titlePage' => 'Dashboard',
            'productsQuantity' => $productsQuantity,
            'ordersQuantity' => $ordersQuantity,
            'costumerQuantity' => $costumerQuantity,
            'totalOrders' => $totalOrders,
        ]);
    }

    /**
     * @Route("/category_banners", name="/app_category_banners")
     */
    public function CategoryBanners(ManagerRegistry $doctrine): Response
    {

        $getCategories = $doctrine->getRepository(Categories::class)->findAll();
        $getSliders = $doctrine->getRepository(Sliderscategory::class)->findAll();



        return $this->render('ADMIN/menu_products/categorybanners.html.twig', [
            'titlePage' => 'Slides Banners',
            'categories' => $getCategories,
            'sliders' => $getSliders
        ]);
    }
    /**
     * @Route("/category_banners-submit", name="/app_category_banners_submit")
     */
    public function CategoryBannersSubmit(ManagerRegistry $doctrine, Request $request): Response
    {

        if(isset($_FILES['file']['name'])) {
            $filename = $_FILES['file']['name'];

            $location = "uploads/sliders/" . $filename;
            $imageFileType = pathinfo($location, PATHINFO_EXTENSION);
            $imageFileType = strtolower($imageFileType);
            $valid_extensions = array("jpg", "jpeg", "png");

            if (in_array(strtolower($imageFileType), $valid_extensions)) {
                $path = "uploads/sliders/".time(). "-" .$_FILES['file']['name'];
                copy($_FILES['file']['tmp_name'], $path);
                $id = $request->get('categoryID');
                $en = $doctrine->getManager();
                $product = $doctrine->getRepository(Sliderscategory::class)->find($id);
                if($product == null){
                    $addImage = new Sliderscategory();
                    $addImage->setIdcategory($id);
                    $addImage->setImage($path);
                    $en->persist($addImage);
                    $en->flush();
                }else{
                    $product->setImage($path);
                    $en->persist($product);
                    $en->flush();
                }

            }
        }


      return new Response();
    }
}
