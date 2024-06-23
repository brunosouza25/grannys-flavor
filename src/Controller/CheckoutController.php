<?php

namespace App\Controller;

use App\Entity\Apporders;
use App\Entity\Guestcontact;
use App\Entity\Guestcontactaddress;
use App\Entity\Housedata;
use App\Entity\Cart;
use App\Entity\Ordercartextras;
use App\Entity\GridCart;
use App\Entity\Orderlist;
use App\Entity\Orderlistextra;
use App\Entity\OrderPayments;
use App\Entity\Orders;
use App\Entity\Products;
use App\Entity\SystemConfig;
use App\Entity\Vivawallet;
use App\Service\EmailService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
class CheckoutController extends AbstractController
{
    private $emailService;
    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * @Route("/web/checkout/result", name="app_checkout_result")
     */
    public function index(ManagerRegistry $doctrine): Response
    {

        $vivavalletUpt = $doctrine->getRepository(Vivawallet::class)->find(1);
        $acesstoken = $vivavalletUpt->getAcesstoken();

        $en = $doctrine->getManager();

        if($transictionCode = @$_GET['id']){
            $order = $doctrine->getRepository(Orders::class)->find($transictionCode);

            $payment = $doctrine->getRepository(OrderPayments::class)->findOneBy(['order_id' => $order->getId()]);
            $payment->setStatus(1);
            $date = new \DateTime(date('Y-m-d H:i:s'));
            $payment->setDate($date);

            $en->persist($payment);

            $orderProducts = $doctrine->getRepository(Orderlist::class)->findBy(['orderid' => $order->getId()]);

            $costumerInfo = $doctrine->getRepository(Guestcontact::class)->find($order->getUserid());

            $getclientAddress = $doctrine->getRepository(Guestcontactaddress::class)->find($order->getDeliveryAddressId());

            $systemConfig = $doctrine->getRepository(SystemConfig::class)->find(1);

            $fee = $systemConfig->getFixedFee();

            $newArray = [];

            foreach ($orderProducts as $product) {
                $productImage = $doctrine->getRepository(Products::class)->find($product->getProductId())->getImage();
                $product->image = $productImage;
                $newArray[] = $product;
            }

            $emailInfo = new \stdClass();
            $emailInfo->orderId = $order->getId();
            $emailInfo->time = $order->getTime();
            $emailInfo->total = bcmul($order->getValue(), 100);
            $emailInfo->products = $newArray;
            $emailInfo->costumerInfo = $costumerInfo;
            $emailInfo->costumerAddress = $getclientAddress;
            $emailInfo->url = $_SERVER['HTTP_HOST'];
            $emailInfo->feePrice = bcmul($fee, 100);

            $email = $this->emailService;
            $email->body($emailInfo, 'ordersAdmin');
            $email->setCostumerInfo([ 'destinationEmail' => $systemConfig->getEmailusername(), 'destinationName' => $systemConfig->getCompanyName()]);
            $email->setSubject("New order number: #" . $order->getId());
            $email->setEmailInfo();

            $email->saveInDataBase();

            $en->flush();

            return $this->render('checkout/index.html.twig', [
                'controller_name' => 'CheckoutController',
                'idOrder' => $order->getId(),
            ]);

        }else{
            return $this->render('checkout/error.html.twig', [
                'again' => '11111',

            ]);
//            echo '<a href="'.$againNumber.'">Tentar de NOVO</a>';
        }
//        }

//        $curl = curl_init();
//        curl_setopt_array($curl, array(
//            CURLOPT_URL => 'https://demo-api.vivapayments.com/checkout/v2/transactions/'.$transictionCode,
//            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_ENCODING => '',
//            CURLOPT_MAXREDIRS => 10,
//            CURLOPT_TIMEOUT => 0,
//            CURLOPT_FOLLOWLOCATION => true,
//            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//            CURLOPT_CUSTOMREQUEST => 'GET',
//            CURLOPT_HTTPHEADER => array(
//                'Authorization: Bearer '.$acesstoken
//            ),
//        ));
//        $response = curl_exec($curl);
//        curl_close($curl);
//        dd($response);




    }


    /**
     * @Route("/confirmed-regular-order/{ordernr}", name="app_confirmed_regular_order")
     */
    public function confirmedRegularOrder(ManagerRegistry $doctrine, Request $request): Response
    {

        $ordernr = $request->get('ordernr');

        return $this->render('checkout/custom.html.twig', [
            'ordernr' => $ordernr,
        ]);
    }

    /**
     * @Route("/send_email", name="/send_email")
     */
    public function send(ManagerRegistry $doctrine, Request $request): Response
    {

        $this->emailService->sendEmailsInDataBase();
        return new Response();
    }

}
