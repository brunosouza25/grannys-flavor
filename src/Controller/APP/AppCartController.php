<?php

namespace App\Controller\APP;

use App\Entity\Appcart;
use App\Entity\Appcartextras;
use App\Entity\Apporderextra;
use App\Entity\Apporderitems;
use App\Entity\Apporders;
use App\Entity\Foodadicionalcategory;
use App\Entity\Foodadicionalitems;
use App\Entity\Foodmenuitemsmultiple;
use App\Entity\Vivawallet;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/** @Route("/app", name="app") */

class AppCartController extends AbstractController
{
    /**
     * @Route("/cart", name="app_cart")
     */
    public function index(Request $request, ManagerRegistry $doctrine): Response
    {
        $getCart = $doctrine->getRepository(Appcart::class)->findBy(array('iduser' => $this->getUser()->getID()));
        $countItems = count($getCart);

        if($getCart == null){
            return $this->render('APP/app_cart/empty.html.twig', [
                'titlePage' => 'Carrinho',
            ]);
        }else{
            $totalPriceItems = 0;
            foreach ($getCart as $priceCart){
                $totalPriceItems = $totalPriceItems + $priceCart->getPrice();
            }

        $getSizes = $doctrine->getRepository(Foodmenuitemsmultiple::class)->findAll();

        $getextras = $doctrine->getRepository(Appcartextras::class)->findAll();
        $getextrasitems = $doctrine->getRepository(Foodadicionalitems::class)->findAll();
        $getextrasCat = $doctrine->getRepository(Foodadicionalcategory::class)->findAll();

        $bag = 1.00;
        $delivery = 2.00;

        $total = ($bag + $delivery + $totalPriceItems);

            return $this->render('APP/app_cart/index.html.twig', [
                'titlePage' => 'Carrinho',
                'qtditem' => $countItems,
                'cartItem' => $getCart,
                'sizes' => $getSizes,
                'extras' => $getextras,
                'extraitems' => $getextrasitems,
                'extracat' => $getextrasCat,
                'bag' => $bag,
                'delivery' => $delivery,
                'subtotal' => $totalPriceItems,
                'total' => $total
            ]);

        }

    }

    /**
     * @Route("/payment-confirm", name="/app_payment_confirm")
     */
    public function PaymentConfirm(ManagerRegistry $doctrine): Response
    {
        return $this->render('APP/app_cart/paymnetmethod.html.twig', [
            'titlePage' => 'Pagamento',
        ]);
    }

    /**
     * @Route("/order-sucess/{ordernumber}", name="/app_order_sucess")
     */
    public function OrderSucess(Request $request, ManagerRegistry $doctrine): Response
    {
        $ordernumber = $request->get('ordernumber');

        $getorder = $doctrine->getRepository(Apporders::class)->findOneBy(array('ordernr' => $ordernumber));

        $getitems = $doctrine->getRepository(Apporderitems::class)->findBy(array('ordernumber' => $ordernumber));
        $getSizes = $doctrine->getRepository(Foodmenuitemsmultiple::class)->findAll();

        $getextras = $doctrine->getRepository(Apporderextra::class)->findBy(array('orderid' => $getorder->getId()));

        $getextrasitems = $doctrine->getRepository(Foodadicionalitems::class)->findAll();



        $getextrasCat = $doctrine->getRepository(Foodadicionalcategory::class)->findAll();


        return $this->render('APP/app_cart/ordersucess.html.twig', [
            'titlePage' => 'Pagamento',
            'order' => $getorder,
            'items' => $getitems,
            'sizes' => $getSizes,
            'extras' => $getextras,
            'extraitems' => $getextrasitems,
            'extracat' => $getextrasCat,
        ]);
    }


    /**
     * @Route("/oder-place-data", name="/app_place_data")
     */
    public function ordePlaceData(Request $request, ManagerRegistry $doctrine): Response
    {
        $ordertype = $request->get('ordertype');
        $dateorder = $request->get('dateorder');
        $timeorder = $request->get('timeorder');
        $paymenttype = $request->get('paymenttype');

        $en = $doctrine->getManager();

        $referenceOrder = rand(000000,999999);

        if($paymenttype != 'online'){

            $getCart = $doctrine->getRepository(Appcart::class)->findBy(array('iduser' => $this->getUser()->getID()));
            $totalPriceItems = 0;
            foreach ($getCart as $priceCart){
                $totalPriceItems = $totalPriceItems + $priceCart->getPrice() * $priceCart->getQtd();
            }

            $getSizes = $doctrine->getRepository(Foodadicionalitems::class)->findAll();


            $getextrasitems = $doctrine->getRepository(Foodadicionalitems::class)->findAll();
            $getextrasCat = $doctrine->getRepository(Foodadicionalcategory::class)->findAll();

            $bag = 1.00;
            $delivery = 2.00;

            $total = ($bag + $delivery + $totalPriceItems);

            $addOrder = new Apporders();
            $addOrder->setUserid($this->getUser()->getId());
            $addOrder->setOrderstate(0);
            $addOrder->setTotal($total);
            $addOrder->setPaymenttype($paymenttype);
            $addOrder->setDateorder($dateorder);
            $addOrder->setTimeorder($timeorder);
            $addOrder->setPaymentstatus(0);
            $addOrder->setOrdercodevw('NONE');
            $addOrder->setOrdernr($referenceOrder);
            $addOrder->setOrdertype($ordertype);
            $addOrder->setTimeprepare(0);
            $en->persist($addOrder);
            $en->flush();


            foreach ($getCart as $valuesitm){
                $addOderItem = new Apporderitems();
                $addOderItem->setOrderid($addOrder->getId());
                $addOderItem->setOrdernumber($addOrder->getOrdernr());
                $addOderItem->setItem($valuesitm->getItem());
                $addOderItem->setQtd($valuesitm->getQtd());
                $addOderItem->setPrice($valuesitm->getPrice());
                $addOderItem->setImage($valuesitm->getImage());
                $addOderItem->setType($valuesitm->getType());

                if($valuesitm->getType() == 'multiple'){
                    $addOderItem->setSizeid($valuesitm->getSizeid());
                }else{
                    $addOderItem->setSizeid('');
                }
                $addOderItem->setComment($valuesitm->getComment());
                $en->persist($addOderItem);

                $en->flush();


            }


            $deletealldata = $doctrine->getRepository(Appcart::class)->findBy(array('iduser' => $this->getUser()->getID()));


            return new JsonResponse([
                'state' => 1,
                'type' => $paymenttype,
                'ordernumber' => $addOrder->getOrdernr()
            ]);

        }else{


            $getCart = $doctrine->getRepository(Appcart::class)->findBy(array('iduser' => $this->getUser()->getID()));
            $totalPriceItems = 0;
            foreach ($getCart as $priceCart){
                $totalPriceItems = $totalPriceItems + $priceCart->getPrice() * $priceCart->getQtd();
            }

            $getSizes = $doctrine->getRepository(Foodadicionalitems::class)->findAll();


            $getextrasitems = $doctrine->getRepository(Foodadicionalitems::class)->findAll();
            $getextrasCat = $doctrine->getRepository(Foodadicionalcategory::class)->findAll();



            $bag = 1.00;
            $delivery = 2.00;

            $total = ($bag + $delivery + $totalPriceItems);

            $addOrder = new Apporders();
            $addOrder->setUserid($this->getUser()->getId());
            $addOrder->setOrderstate(0);
            $addOrder->setTotal($total);
            $addOrder->setPaymenttype($paymenttype);
            $addOrder->setDateorder($dateorder);
            $addOrder->setTimeorder($timeorder);
            $addOrder->setPaymentstatus(0);
            $addOrder->setOrdercodevw('NONE');
            $addOrder->setOrdernr($referenceOrder);
            $addOrder->setOrdertype($ordertype);
            $addOrder->setTimeprepare(0);
            $en->persist($addOrder);
            $en->flush();


            foreach ($getCart as $valuesitm){
                $addOderItem = new Apporderitems();
                $addOderItem->setOrderid($addOrder->getId());
                $addOderItem->setOrdernumber($addOrder->getOrdernr());
                $addOderItem->setItem($valuesitm->getItem());
                $addOderItem->setQtd($valuesitm->getQtd());
                $addOderItem->setPrice($valuesitm->getPrice());
                $addOderItem->setImage($valuesitm->getImage());
                $addOderItem->setType($valuesitm->getType());

                if($valuesitm->getType() == 'multiple'){
                    $addOderItem->setSizeid($valuesitm->getSizeid());
                }else{
                    $addOderItem->setSizeid('');
                }
                $addOderItem->setComment($valuesitm->getComment());
                $en->persist($addOderItem);

                $en->flush();


            }

            $vivawallet = $doctrine->getRepository(Vivawallet::class)->find(1);
            $credentials = $vivawallet->getClientid().":".$vivawallet->getSecret();

            $url = 'https://accounts.vivapayments.com/connect/token';

            if ($_ENV['APP_ENV'] == 'dev') {
                $url = 'https://demo-accounts.vivapayments.com/connect/token';
            }

            $ch = curl_init();
            $options = array(
                CURLOPT_URL => $url,
                CURLOPT_POST => 1,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_HEADER => false,
                // Set the auth type as `Basic`
                CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
                // Set login and password for Basic auth
                CURLOPT_USERPWD => $credentials,

                CURLOPT_HTTPHEADER => array(
                    'Accept: application/json',
                    'Content-Type: application/x-www-form-urlencoded'
                ),
                // To send additional parameters in the POST body
                CURLOPT_POSTFIELDS => "grant_type=client_credentials"
            );
            curl_setopt_array($ch, $options);
            $result = curl_exec($ch);
            $result = json_decode($result);
            curl_close($ch);

            $acessToken = $result->access_token;

            $vivavalletUpt = $doctrine->getRepository(Vivawallet::class)->find(1);
            $vivavalletUpt->setAcesstoken($acessToken);
            $doctrine->getManager()->persist($vivavalletUpt);
            $doctrine->getManager()->flush();



            return new JsonResponse([
                'acessToken' => $acessToken,
                'reference' => $referenceOrder,
                'type' => $paymenttype,
            ]);


        }




    }

    /**
     * @Route("/payment-ordercode-vw", name="/app_payment_order_code")
     */
    public function PaymentOrderCodevW(Request $request, ManagerRegistry $doctrine): Response
    {

        $accessToken = $request->get('acessToken');
        $referenceOrder = $request->get('reference');

        $getOrder = $doctrine->getRepository(Apporders::class)->findOneBy(array('ordernr' => $referenceOrder));

        $saco = 1;
        $taxadelivery = 2;

        $totalPayC = ($getOrder->getTotal() + $saco + $taxadelivery);
        $amouttoPay = bcmul($totalPayC, 100);


        $postFields  = [
            'amount'              => $amouttoPay,
            'customerTrns'        => 'Fabios RoadStop - Pagamento da Encomenda #'.$referenceOrder,
            'customer'            => [
                'email'       => $this->getUser()->getEmail(),
                'fullName'    => $this->getUser()->getName(),
                'phone'       => $this->getUser()->getPhone(),
                'countryCode' => 'PT',
                'requestLang' => 'pt-PT'
            ],
            'paymentTimeout'      => 1800,
            'preauth'             => true,
            'allowRecurring'      => true,
            'maxInstallments'     => 0,
            'paymentNotification' => true,
            'tipAmount'           => 0,
            'disableExactAmount'  => false,
            'disableCash'         => false,
            'disableWallet'       => false,
            'sourceCode'          => 'Default',
            'merchantTrns'        => 'Encomenda #'.$referenceOrder,
            'tags'                => ['tag1', 'tag2']
        ];

//        desenvolvimento
        $url = 'https://api.vivapayments.com/checkout/v2/orders';

        if ($_ENV['APP_ENV'] == 'dev') {
            $url = 'https://demo-api.vivapayments.com/checkout/v2/orders';
        }
//        produção

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS     => json_encode($postFields),
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$accessToken,
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        $response = json_decode($response);

        $orderCode = $response->orderCode;

        return  new JsonResponse([
            'ordercode' => $orderCode,
            'env' => $_ENV['APP_ENV']
        ]);

    }

}
