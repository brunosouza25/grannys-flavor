<?php

namespace App\Controller\APP;

use App\Entity\Apporderitems;
use App\Entity\Apporders;
use App\Entity\UserAddress;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/** @Route("/app", name="app") */

class AppOrdersController extends AbstractController
{
    /**
     * @Route("/app/orders", name="/app_orders")
     */
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {

        $getOrders = $doctrine->getRepository(Apporders::class)->findBy(array('userid' => $this->getUser()->getid()));
        return $this->render('APP/app_orders/index.html.twig', [
            'titlePage' => 'Pedidos',
            'orders' => $getOrders
        ]);
    }

    /**
     * @Route("/app/orders/detail/{orderid}", name="/app_orders_detail")
     */
    public function OrderDetails(Request $request, ManagerRegistry $doctrine): Response
    {

        $orderid = $request->get('orderid');

        $getOrders = $doctrine->getRepository(Apporders::class)->find($orderid);
        $getitemorder = $doctrine->getRepository(Apporderitems::class)->findBy(array('orderid' => $orderid));

        $bag = '1';
        $delivery = '2';

        $totalPriceItems = 0;
        foreach ($getitemorder as $priceCart){
            $totalPriceItems = $totalPriceItems + $priceCart->getPrice();
        }

        $bag = 1.00;
        $delivery = 2.00;

        $total = ($bag + $delivery + $totalPriceItems);

        $getaddress = $doctrine->getRepository(UserAddress::class)->findOneBy(array('userid' => $this->getUser()->getid()));


        return $this->render('APP/app_orders/detail.html.twig', [
            'titlePage' => 'Pedidos',
            'orders' => $getOrders,
            'orderitems' => $getitemorder,
            'bag' => $bag,
            'delivery' => $delivery,
            'total' => $total,
            'address' => $getaddress
        ]);
    }
}
