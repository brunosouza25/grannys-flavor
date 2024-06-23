<?php

namespace App\Controller\Confirmations;

use App\Controller\APP\AppOrdersController;
use App\Entity\Apporderitems;
use App\Entity\Apporders;
use App\Entity\OrderPayments;
use App\Entity\Products;
use App\Entity\Foodmenuitemsmultiple;
use App\Entity\Guestcontact;
use App\Entity\Guestcontactaddress;
use App\Entity\Cart;
use App\Entity\Ordercartextras;
use App\Entity\GridCart;
use App\Entity\Orderlist;
use App\Entity\Orderlistextra;
use App\Entity\Orders;
use App\Entity\ProductsGrid;
use App\Entity\SystemConfig;
use App\Entity\User;
use App\Entity\UserAddress;
use App\Entity\Zonesoftapi;
use App\Service\ProductsService;
use Doctrine\Persistence\ManagerRegistry;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Ramsey\Uuid\Uuid;

/** @Route("/confirmation", name="confirmation/") */
class ConfirmationDashboardController extends AbstractController
{
    private $productService;
    public function __construct(ProductsService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * @Route("/dashboard", name="app_confirmation_dashboard")
     */
    public function index(ManagerRegistry $doctrine): Response
    {

        $OrderToConfirm = $doctrine->getRepository(Apporders::class)->findBy(array('orderstate' => 0));
        $qtdOrderToConfirm = count($OrderToConfirm);

        $OrderPrepare = $doctrine->getRepository(Apporders::class)->findBy(array('orderstate' => 1));
        $qtdPrepare = count($OrderPrepare);

        $OrderToDelivery = $doctrine->getRepository(Apporders::class)->findBy(array('orderstate' => 2));
        $qtdToDelivery = count($OrderToDelivery);

        $OrderToPickUp = $doctrine->getRepository(Apporders::class)->findBy(array('orderstate' => 3, 'ordertype' => 'takeaway'));
        $qtdToPickup = count($OrderToPickUp);

        $en = $doctrine->getManager();

        $qb = $en->createQueryBuilder();

        $query = $qb->select('a')
            ->from('App\Entity\Apporders', 'a')
            ->where('a.orderstate = 3')
            ->andWhere("a.ordertype != 'takeaway'")
            ->getQuery();

        $OrderinDelivery = $query->getResult();

        $qtdOrderinDelivery = count($OrderinDelivery);

        $getaUserAddress = $doctrine->getRepository(UserAddress::class)->findAll();

        $orderItems = $doctrine->getRepository(Apporderitems::class)->findAll();

        $orders = $doctrine->getRepository(Orders::class)->findBy(array('status' => '0'));
        $ordersPrepare = $doctrine->getRepository(Orders::class)->findBy(array('status' => '1'));
        $ordersDeliver = $doctrine->getRepository(Orders::class)->findBy(array('status' => '2'));

        $guest = $doctrine->getRepository(Guestcontact::class)->findAll();
        $guestAddres = $doctrine->getRepository(Guestcontactaddress::class)->findAll();

        $orderlist = $doctrine->getRepository(Orderlist::class)->findAll();

        $orderlistExtra = $doctrine->getRepository(Orderlistextra::class)->findAll();

        return $this->render('Confirmations/confirmation_dashboard/index.html.twig', [
            'qtdtoconfirm' => $qtdOrderToConfirm,
            'orderstoconfirm'=> $OrderToConfirm,
            'allusers' => $guest,
            'getuseraddress' => $getaUserAddress,
            'orderItems' => $orderItems,
            'orderPrepare' => $OrderPrepare,
            'orderqtdPrepare' => $qtdPrepare,
            'orderToDelivery' => $OrderToDelivery,
            'qtdtodelivery' => $qtdToDelivery,
            'ordertoPickup' => $OrderToPickUp,
            'qtdpickup' => $qtdToPickup,
            'orderindelivery' => $OrderinDelivery,
            'qtdindelivery' => $qtdOrderinDelivery,
            'title' => 'titlePage',
            'orders' => $orders,
            'prepare' =>$ordersPrepare,
            'deliver' => $ordersDeliver,
            'guests' => $guest,
            'guestaddress' => $guestAddres,
            'orderlist' => $orderlist,
            'orderlistExtra' => $orderlistExtra
        ]);
    }


    /**
     * @Route("/send_zone_soft", name="send_zone_soft")
     */
    public function sendZoneSoft(ManagerRegistry $doctrine, Request $request): Response
    {
        $en = $doctrine->getManager();

        $orderId = $request->get('orderId');

        $uuid = Uuid::uuid4()->toString();

        $order = $doctrine->getRepository(Orders::class)->find($orderId);
        $orderPayments = $doctrine->getRepository(OrderPayments::class)->findBy(['order_id' => $orderId]);
        $orderProducts = $doctrine->getRepository(Orderlist::class)->findBy(['orderid' => $orderId]);
        $customer = $en->getRepository(Guestcontact::class)->findOneBy(['id' => $order->getUserid()]);
        $customerAddress = $en->getRepository(Guestcontactaddress::class)->find($order->getDeliveryAddressId());

        $url = 'https://zsroi.zonesoft.org/v1.0/integration/order';

        date_default_timezone_set("Europe/Lisbon");

        if ($order->getPaymenttype() == '0') {
            $paymentMethod = 3;
            $paymentType = "Multibanco";
        } else {
            $paymentMethod = 1;
            $paymentType = "CASH";
        }


        $tokendata = $en->getRepository(Zonesoftapi::class)->find(1);
        $storeId = $tokendata->getStoreId();

        $appsecret = $tokendata->getAppsecret();
        $appkey = $tokendata->getAppkey();

        $totalOrdered = 0;
        $newarray = array();
        

        foreach ($orderProducts as $prideold) {

            $totalOrdered = $totalOrdered + $prideold->getPrice() * $prideold->getQtd();

            if (!is_null($prideold->getProductGridId())) {
                $zoneSoftCode = $en->getRepository(ProductsGrid::class)->findOneBy(['id' => $prideold->getProductGridId()])->getCode();

            } else {
                $zoneSoftCode = $en->getRepository(Products::class)->findOneBy(['id' => $prideold->getProductId()])->getCode();
            }


            $product = new \stdClass();

            $product->quantity = (string)$prideold->getQtd();
            $product->price = (string)(bcmul($prideold->getPrice(), 100));
            $product->discount = 0;
            $product->name = (string)$prideold->getItem();
            $product->id = (string)$zoneSoftCode;

            $newarray[] = $product;

        }

        $total = 0;

        $fee = bcmul($doctrine->getRepository(SystemConfig::class)->find(1)->getFixedFee(), 100);

        foreach ($orderPayments as $orderPayment) {
            $total += $orderPayment->getTotal();
        }

        $customerTelephone = $customer->getContact();
        $customerNif = $order->getNif();
        $customerName = $customer->getName() . ' ' . $customer->getLastname();

        $typeOrder = strtoupper($order->getOrdertype()) == '1' ? 'DELIVERY' : 'PICKUP';
        $payload = new \stdClass();

        $payload->order_id = (string)$uuid;
        $payload->store_id = $storeId;
        $payload->type_order = $typeOrder;
        $payload->order_time = $date = date("Y-m-d H:i:s");
        $payload->estimated_pickup_time = $date = date("Y-m-d H:i:s");
        $payload->payment_method = $paymentType;
        $payload->currency = "EUR";
        $payload->delivery_fee = $fee;
        $payload->estimated_total_price = 100;

        /**
         * Quem vai entregar
         */

        $systemConfig = $doctrine->getRepository(SystemConfig::class)->find(1);

        $payload->courier = [
            "name" => $systemConfig->getCompanyName(),
            "phone_number" => $systemConfig->getPhone1(),
            "license_plate" => "AA-00-AA"
        ];

        $payload->customer = ["name" => $customerName, "phone_number" => $customerTelephone, "nif" => $customerNif];
        $payload->products = $newarray;


        $payload->obs = '';
        $payload->orderIsAlreadyPaid = $orderPayments[0]->getStatus() == 1;

        $payload->payment_type = $paymentMethod;

        if ($typeOrder == 'DELIVERY') {
            $payload->delivery_address = ["label" => $customerAddress->getStreet() . '-' . $customerAddress->getCity() . ' - ' . $customerAddress->getPostalcode(), "latitude" => $customerAddress->getLantitude(), "longitude" => $customerAddress->getLongitude()];
            $payload->is_picked_up_by_customer = false;
        } else {
            $payload->delivery_address = ["label" => 'NO DELIVERY' . '-' . 'NO DELIVERY' . ' - ' . 'NO DELIVERY', "latitude" => 'NO DELIVERY', "longitude" => 'NO DELIVERY'];
            $payload->is_picked_up_by_customer = true;
        }

        $payload->discounted_products_total = 0;
        $payload->total_customer_to_pay = $fee + $total;

        $payload = json_encode($payload);

        $signature = hash_hmac('SHA256', $payload, $appsecret);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: ' . $appkey,
            'X-Integration-Signature: ' . $signature
        ));
        $result = curl_exec($ch);
        curl_close($ch);

        $stateResult = json_decode($result);
//        dd($stateResult);

        if ($stateResult->Response->StatusCode != 201) {
            return new JsonResponse([
                'status' => 0,
            ]);
        }

        foreach ($orderProducts as $prideold) {
                $this->productService->syncProductsStock($prideold->getProductId());
        }

        $order->setZoneSoftSending(1);
        $en->flush();
        return new JsonResponse([
            'status' => 1,
        ]);

    }


    /**
     * @Route("/confirm-order", name="app_confirmation_order")
     */
    public function confirmOrder(ManagerRegistry $doctrine, Request $request): Response
    {
        $reference = $request->get('referenceOrder');
        $getOrderaData = $doctrine->getRepository(Apporders::class)->findOneBy(array('ordernr' => $reference));

        $url = 'https://zsroi.zonesoft.org/v1.0/integration/order';

        date_default_timezone_set("Europe/Lisbon");


        $paymentMethod = 1;
        if($paymentMethod == 1){
            $paymentType = "CASH";
        }else{
            $paymentType = "Multibanco";
        }


        $en = $doctrine->getManager();


        $tokendata = $en->getRepository(Zonesoftapi::class)->find(1);
        $storeId = $tokendata->getStoreId();


        $appsecret = $tokendata->getAppsecret();
        $appkey = $tokendata->getAppkey();

        $totalOrdered = 0;
        $newarray = array();
        $orderItems = $en->getRepository(Apporderitems::class)->findBy(['orderid' => $getOrderaData->getid()]);
//        $orderExtras = $en->getRepository(Products::class)->findBy(['orderid' => $getOrderaData->getid()]);
//        $orderItems = $en->getRepository(Apporders::class)->findBy(['orderid' => $request->get('orderid')]);
//        $orderItems = $en->getRepository(Apporderitems::class)->findBy(['orderid' => $request->get('orderid')]);
//        $orderItems = $en->getRepository(Apporderitems::class)->findAll();
        foreach ($orderItems as $prideold){

            $totalOrdered = $totalOrdered + $prideold->getPrice();
            $type = $en->getRepository(Products::class)->findOneBy(['id' => $prideold->getProductCode()])->getType();
            if ($type == 'multiple') {

                $extras = $en->getRepository(Orderlistextra::class)->findBy(['orderid' => $prideold->getId()]);
                foreach ($extras as $extra) {
                    $newarray[] = array(
                        'quantity' => (string)$extra->getQtd(),
                        'price' => (string)(bcmul($extra->getPrice(),100)),
                        'discount' => '0',
                        'name' => $extra->getName(),
                        'id' => (string)$extra->getIdZoneSoft()
                    );

                }
//                dd($prideold);
//                $zoneSoftCode = $en->getRepository(Foodmenuitemsmultiple::class)->findOneBy(['id' => $prideold->getProductCode()])->getZonesoftcode();

            } else {
                $zoneSoftCode = $en->getRepository(Products::class)->findOneBy(['id' => $prideold->getProductCode()])->getZonesoftcode();
                $newarray[] = array(
                    'quantity' => (string)$prideold->getQtd(),
                    'price' => (string)(bcmul($prideold->getPrice(),100)),
                    'discount' => '0',
                    'name' => $prideold->getItem(),
                    'id' => (string)$zoneSoftCode
                );
            }

        }

        $customer = $en->getRepository(Guestcontact::class)->findOneBy(['id' => $getOrderaData->getUserid()]);
        $customerTelephone = $customer->getContact();
        $customerName = $customer->getName() . ' ' . $customer->getLastname();


        $payload = array(
            "order_id" => $getOrderaData->getid(),
            "store_id" => $storeId,
            "type_order" => "PICKUP",
            "order_time" => $date=date("Y-m-d H:i:s"),
            "estimated_pickup_time" => $date=date("Y-m-d H:i:s"),
            "order_time" => "2020-11-09 12:24:53",
            "estimated_pickup_time" => "2020-11-09 12:24:53",
            "payment_method" => $paymentType,
            "currency" => "EUR",
            "delivery_fee" => 0,
            "estimated_total_price" => 100,
            "courier" => array(
                "name" => "Flash",
                "phone_number" => "925947647",
                "license_plate" => "AA-00-AA"
            ),
            "customer" => array(
                "name" => $customerName,
                "phone_number" => $customerTelephone,
                "nif" => ""
            ),
            "products" => $newarray,
            "obs" => '',
            "orderIsAlreadyPaid" => false,
            "payment_type" => $paymentType,
            "delivery_address" => array(
                "label" => "Praceta do Município de São Domingos 2, 8400-415 Lagoa",
                "latitude" => "37.13637891839967",
                "longitude" => "-8.459192919573084"
            ),
            "is_picked_up_by_customer" => false,
            "discounted_products_total" => 0,
            "total_customer_to_pay" => 100
        );
        $payload = json_encode($payload);
        $signature = hash_hmac('SHA256', $payload, $appsecret);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: '.$appkey,
            'X-Integration-Signature: '.$signature
        ));
//        $result = curl_exec($ch);
//
//        curl_close($ch);
//        $stateResult =  json_decode($result);
//        if ($stateResult->Response->StatusCode != 201) {
//            return new JsonResponse([
//                'status' => 0,
//            ]);
//        }

        /**
         * divisão
         *
         */
        $timetoprepare = $request->get('timetoprepare');


        $en = $doctrine->getManager();

        $getOrderaData->setOrderstate(1);
        $getOrderaData->setTimeprepare($timetoprepare);
        $en->persist($getOrderaData);
        $en->flush();

        $getOrderList = $doctrine->getRepository(Apporderitems::class)->findBy(array('orderid' => $getOrderaData->getId()));
        $userdata = $doctrine->getRepository(Guestcontact::class)->find($getOrderaData->getUserid());
        $getclientAddress = $doctrine->getRepository(Guestcontactaddress::class)->findOneBy(array('idcontact'=> $userdata->getId()));

//        $emailUserName = $doctrine->getRepository(SystemConfig::class)->findOneBy(['id' => 0]);
        $mail = new PHPMailer(true);
        $mail->CharSet = "UTF-8";

            //Server settings
            $mail->SMTPDebug  = SMTP::DEBUG_OFF;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'mail.pitombolanches.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = 'orders@pitombolanches.com';                     //SMTP username
            $mail->Password   = 'l%]u1jt)ko(y';                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('orders@pitombolanches.com', 'Pedidos - Pitombo Lanches');
            $mail->addAddress($userdata->getEmail(), $userdata->getName());     //Add a recipient
            $mail->addReplyTo('orders@pitombolanches.com', 'Duvidas');

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Encomenda Nr: '.$getOrderaData->getOrdernr().' Pitombo Lanches';

            $mail-> Body ='<html>
                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;border:0;max-width:600px">
        <tbody>
        <tr>
            <td valign="top" id="m_-7190579756368962722m_-1793210779212732005templateHeader" style="background-color:#262b32;background-image:none;background-repeat:no-repeat;background-position:center;background-size:cover;border-top:0;border-bottom:0;padding-top:9px;padding-bottom:0" bgcolor="#262b32">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;min-width:100%">
                    <tbody>
                    <tr>
                        <td valign="top">
                            <table align="left" border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse;max-width:100%;min-width:100%" width="100%">
                                <tbody>
                                <tr>
                                    <td valign="top" style="word-break:normal;font-size:16px;line-height:150%;padding:13px 13px 18px 25px;color:#ffffff;font-family:Arial,Helvetica Neue,Helvetica,sans-serif;text-align:center" align="center">
                                        <h1 style="display:block;margin:0;padding:0;color:#f8f8f8;font-family:\'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif;font-size:26px;font-style:normal;font-weight:normal;line-height:125%;letter-spacing:normal;text-align:center"><span class="il">Pitombo Lanches</span> - Pedido # '.$getOrderaData->getOrdernr().'</h1>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>';
            $mail -> Body .='
                         <tr>
            <td valign="top" id="m_-7190579756368962722m_-1793210779212732005templateBody" style="background-color:#ffffff;background-image:none;background-repeat:no-repeat;background-position:center;background-size:cover;border-top:0;border-bottom:2px solid #eaeaea;padding-top:0;padding-bottom:9px" bgcolor="#ffffff">
                <br>
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse">
                    <tbody>
                    <tr>
                        <td valign="top">
                            <table align="left" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse:collapse">
                                <tbody>
                                <tr>
                                    <td valign="top" style="word-break:normal;color:#606060;font-family:Arial;font-size:14px;line-height:150%;padding:9px 18px;text-align:left" align="left">
                                        <div>
                                       <span id="m_-7190579756368962722m_-1793210779212732005docs-internal-guid-6035f703-8c06-2131-54a9-a468a12bfaa8">
                                          <span style="background-color:transparent;font-family:arial;font-size:14px;vertical-align:baseline">
                                             <table style="border-collapse:collapse;width:100%" width="100%">
                                                <tbody>
                                                   <tr>
                                                      <td>
                                                         <div style="text-align:center">
                                                            Novo pedido aceite<br><strong>ENTREGA</strong>
                                                            <br>
                                                            '.$timetoprepare.' minutos&nbsp;&nbsp;
                                                         </div>
                                                      </td>
                                                      <td valign="top">
                                                         <div style="text-align:center">
                                                            Total<br>
                                                            <strong>'.$getOrderaData->getTotal().'&nbsp;€
                                                            
                                                            </strong>
                                                         </div>
                                                      </td>
                                                   </tr>
                                                </tbody>
                                             </table>
                                             <hr style="margin-top:18px;margin-bottom:18px;height:1px;border:0;border-top:1px solid #ccc">';

            $mail->Body .='
                                                               <table align="left" border="0" cellpadding="0" cellspacing="0" width="260" style="border-collapse:collapse">
                                                <tbody>
                                                   <tr>
                                                      <td style="padding-top:0px;padding-left:18px;padding-bottom:0px;padding-right:0">
                                                         <table border="0" cellpadding="9" cellspacing="0" width="100%" style="border-collapse:collapse">
                                                            <tbody>
                                                               <tr>
                                                                  <td valign="top" style="word-break:normal;color:#606060;font-family:Arial;font-size:14px;line-height:150%">
                                                                     <span style="line-height:150%">
                                                                     <strong style="line-height:20.7999992370605px">'.$userdata->getName().'</strong>
                                                                     <br>
                                                                     <strong><a href="tel:+351 934 869 755" style="color:#5ac15e;font-weight:normal;text-decoration:underline" rel="noreferrer" target="_blank">+351'.$userdata->getContact().'</a></strong>
                                                                     <br>
                                                                     
                                                                     <a href="mailto:'.$userdata->getEmail().'" rel="noreferrer" target="_blank">'.$userdata->getEmail().'</a>
                                                                     <br>
                                                                     
                                                                    ';

            if (!empty($getclientAddress)){
                $mail->Body .= '<a href="https://maps.google.com/maps?q='.$getclientAddress->getLantitude().','.$getclientAddress->getLongitude().'">'.$getclientAddress->getStreet().'<br> '.$getclientAddress->getCity().'<br> '.$getclientAddress->getPostalcode().'</a>';
            }

            $mail->Body .=
                                                                     '</span>
                                                                     <br><br>
                                                                     <span style="line-height:150%">
                                                                     Observações: <strong style="white-space:pre-wrap">-</strong>
                                                                     </span>
                                                                  </td>
                                                               </tr>
                                                            </tbody>
                                                         </table>
                                                      </td>
                                                   </tr>
                                                </tbody>
                                             </table>';

            $mail->Body .='
                                                                     <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse">
                                                <tbody>
                                                   <tr>
                                                      <td valign="top">
                                                         <table align="left" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse:collapse">
                                                            <tbody>
                                                               <tr>
                                                                  <td style="padding-top:9px;padding-left:18px;padding-bottom:0px;padding-right:18px">
                                                                     <table style="border-collapse:collapse;border-bottom:1px solid #999999" cellpadding="0" cellspacing="0" width="100%">
                                                                        <tbody>
                                                                           <tr>
                                                                              <td valign="top">
                                                                                 <table width="100%" border="0" style="border-collapse:collapse;word-break:normal;color:#606060;font-family:Arial;font-size:14px;line-height:150%">
                                                                                    <tbody>
                                                                                       <tr style="line-height:110%">
                                                                                          <td style="text-align:left" nowrap="" align="left"><strong><span>Qtde</span></strong></td>
                                                                                          <td style="text-align:left" align="left"><strong><span>Itens</span></strong></td>
                                                                                          <td style="text-align:right" align="right"><strong><span>Preço (EUR)</span></strong></td>
                                                                                       </tr>';



            foreach ($getOrderList as $valueL) {
                $getOrderListExtraSize = $doctrine->getRepository(Foodmenuitemsmultiple::class)->find($valueL->getProductCode());
                $mail->Body .='<tr style="border-top:1px solid #999999;line-height:110%">
                                                                                          <td style="text-align:left" align="left"><span>'.$valueL->getQtd().'x</span></td>
                                                                                          <td style="text-align:left" align="left"><span><span class="il">'.$valueL->getItem().'</span></td>
                                                                                          <td style="text-align:right" align="right"><span>'.$valueL->getPrice().'&nbsp;€</span></td>
                                                                                       </tr>';


                if (!is_array($getOrderListExtraSize) & !is_null($getOrderListExtraSize)) {
                    $mail->Body .='
                                 <tr style="line-height:110%">
                                  <td>&nbsp;</td>
                                   <td>
                                  <span>Tamanho: <strong>'.$getOrderListExtraSize->getItemname().' + '.$getOrderListExtraSize->getItemprice().'</strong></span>
                                  </td>
                                  <td style="text-align:right" align="right">&nbsp;</td>
                                  </tr>
                ';
                } else if (is_array($getOrderListExtraSize)) {
                    foreach ($getOrderListExtraSize as $valueS) {

                        $mail->Body .= '
                                 <tr style="line-height:110%">
                                  <td>&nbsp;</td>
                                   <td>
                                  <span>Tamanho: <strong>' . $valueS->getItemname() . ' + ' . $valueS->getItemprice() . '</strong></span>
                                  </td>
                                  <td style="text-align:right" align="right">&nbsp;</td>
                                  </tr>
                ';

                    }

                }

            }


            $mail-> Body .='
                                                                            
                                                                         
                                                                                                       <tr style="border-top:1px solid #999999;line-height:110%">
                                                                                          <td>&nbsp;</td>
                                                                                          <td style="text-align:left" align="left"><span style="text-transform:uppercase">
                                                                                             Taxa de entrega  (23% incluído):
                                                                                             </span>
                                                                                          </td>
//                                                                                          <td style="text-align:right" align="right"><span>5.00&nbsp;€</span></td>
                                                                                       </tr>
                                                                                       <tr style="line-height:110%">
                                                                                          <td>&nbsp;</td>
                                                                                          <td style="text-align:left" align="left"><span style="text-transform:uppercase">Bag</span></td>
                                                                                          <td style="text-align:right" align="right"><span>1.00&nbsp;€</span></td>
                                                                                       </tr>
                                                                      
                                                                                       <tr style="line-height:110%">
                                                                                          <td>&nbsp;</td>
                                                                                          <td style="text-align:left" align="left"><span style="text-transform:uppercase"><strong>Total</strong></span></td>
                                                                                          <td style="text-align:right" align="right"><span><strong>'.$getOrderaData->getTotal().'&nbsp;€</strong></span></td>
                                                                                       </tr>';




            $mail->Body .='                                                                                    </tbody>
                                                                                 </table>
                                                                              </td>
                                                                           </tr>
                                                                        </tbody>
                                                                     </table>
                                                                  </td>
                                                               </tr>
                                                            </tbody>
                                                         </table>
                                                      </td>
                                                   </tr>
                                                </tbody>
                                             </table>
                                             <p style="margin:10px 0;padding:0;color:#606060;font-family:Arial;font-size:14px;line-height:150%;text-align:center"><br>-</p>
                                          </span>
                                       </span>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>';


            $mail -> Body .='
                 <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse">
                    <tbody>
                    <tr>
                        <td valign="top">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;min-width:100%">
                                <tbody>
                                <tr>
                                    <td valign="top">
                                        <table align="left" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;min-width:100%">
                                            <tbody>
                                            <tr>
                                                <td style="padding-top:9px;padding-left:18px;padding-bottom:9px;padding-right:18px">
                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;min-width:100%;border:1px solid #dedede">
                                                        <tbody>
                                                        <tr>
                                                            <td valign="top">
                                                                <table align="left" border="0" cellpadding="0" cellspacing="0" width="310" style="border-collapse:collapse">
                                                                    <tbody>
                                                                    <tr>
                                                                        <td style="padding-top:0px;padding-right:18px;padding-bottom:0px;padding-left:0px">
                                                                            <table border="0" cellpadding="18" cellspacing="0" width="100%" style="border-collapse:collapse;min-width:100%">
                                                                                <tbody>
                                                                                <tr>
                                                                                    <td valign="top" style="word-break:normal;color:#606060;font-family:Arial;line-height:150%;font-size:12px;font-weight:normal;text-align:left" align="left">
                                                                                        <div style="text-align:left">Distribuído por:<br><a href="http://app.pitombolanches.com" style="color:#5ac15e;font-weight:normal;text-decoration:underline" rel="noreferrer" target="_blank" data-saferedirecturl="https://www.google.com/url?q=http://app.pitombolanches.com&amp;source=gmail&amp;ust=1663423092009000&amp;usg=AOvVaw2QnCYPUbCH5eaoXEBVv5mp"></a>
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                                </tbody>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                                <table align="left" border="0" cellpadding="0" cellspacing="0" width="252" style="border-collapse:collapse">
                                                                    <tbody>
                                                                    <tr>
                                                                        <td style="padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px">
                                                                            <table border="0" cellpadding="18" cellspacing="0" width="100%" style="border-collapse:collapse;min-width:100%">
                                                                                <tbody>
                                                                                <tr>
                                                                                    <td valign="top" style="word-break:normal;color:#606060;font-family:Arial;line-height:150%;font-size:12px;font-weight:normal;text-align:left" align="left">
                                                                                        <div style="width:212px"><a href="mailto:orders@pitombolanches.com" rel="noreferrer" target="_blank">orders@pitombolanches.com</a></div>
                                                                                        <span>Contacto: </span><a href="http://app.pitombolanches.com" style="font-weight:normal;text-decoration:underline;color:#5ac15e" rel="noreferrer" target="_blank" data-saferedirecturl="https://www.google.com/url?q=http://app.pitombolanches.com&amp;source=gmail&amp;ust=1663423092009000&amp;usg=AOvVaw2QnCYPUbCH5eaoXEBVv5mp">app.pitombolanches.com</a>
                                                                                    </td>
                                                                                </tr>
                                                                                </tbody>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr>
            <td valign="top" id="m_-7190579756368962722m_-1793210779212732005templateFooter" style="background-color:#f0efed;background-image:none;background-repeat:no-repeat;background-position:center;background-size:cover;border-top:0;border-bottom:0;padding-top:9px;padding-bottom:9px" bgcolor="#f0efed">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;min-width:100%">
                    <tbody>
                    <tr>
                        <td valign="top" style="padding-top:9px">
                            <table align="left" border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse;max-width:100%;min-width:100%" width="100%">
                                <tbody>
                                <tr>
                                    <td valign="top" style="word-break:normal;color:#656565;font-family:Helvetica;font-size:12px;line-height:150%;text-align:center;padding-top:0;padding-right:18px;padding-bottom:9px;padding-left:18px" align="center">
                                        Número ID: 208173
                                        |
                                        Nome: <span class="il">Pitombo Lanches</span> — Rua Dr. Manuel de Arriaga, edifício mar salgado, lote 1 loja E, 8365-140 ARMAÇÃO DE PÊRA
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        </tbody>
    </table> </html>';



//          $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
            $mail->send();

//          $session = $_COOKIE["session"];
//            $getdataCart = $doctrine->getRepository(Cart::class)->findAll();
//
//            foreach ($getdataCart as $valuscart){
//                $delorderextras = $doctrine->getRepository(Ordercartextras::class)->findBy(array('idorder' => $valuscart->getId()));
//                $delorderextrasm = $doctrine->getRepository(GridCart::class)->findBy(array('idorder' => $valuscart->getId()));
//                foreach ($delorderextras as $valdel1){
//                    $doctrine->getManager()->remove($valdel1);
//
//                }
//                foreach ($delorderextrasm as $valdel2){
//                    $doctrine->getManager()->remove($valdel2);
//
//                }
//                $doctrine->getManager()->remove($valuscart);
//                $doctrine->getManager()->flush();
//            }
//            $doctrine->getManager()->flush();



            //ENVIO PARA ZONESOFT

//            $url = 'https://zsroi.zonesoft.org/v1.0/integration/order';
//
//            date_default_timezone_set("Europe/Lisbon");
//
//
//
//            $paymentType = "CASH";
//
////            if($paymentMethod == 1){
////                $paymentType = "CASH";
////            }else{
////                $paymentType = "Multibanco";
////            }
//
//
//            $en = $doctrine->getManager();
//
//
//            $tokendata = $en->getRepository(Zonesoftapi::class)->find(1);
//            $storeId = $tokendata->getStoreId();
//
//
//            $appsecret = $tokendata->getAppsecret();
//            $appkey = $tokendata->getAppkey();
//
//            $totalOrdered = 0;
//            $newarray = array();
//
//
//            $newarray[] = array(
//                'quantity' => 1,
//                'price' => 100,
//                'discount' => '0',
//                'name' => "Hamburger",
//                'id' => "121"
//            );
//
//
//            $order_id = rand(100,5000);
//            $order_id = "$order_id";
//
//            $payload = array(
//                "order_id" => $order_id,
//                "store_id" => $storeId,
//                "type_order" => "PICKUP",
////            "order_time" => $date=date("Y-m-d H:i:s"),
////            "estimated_pickup_time" => $date=date("Y-m-d H:i:s"),
//                "order_time" => "2020-11-09 12:24:53",
//                "estimated_pickup_time" => "2020-11-09 12:24:53",
//                "payment_method" => $paymentType,
//                "currency" => "EUR",
//                "delivery_fee" => 0,
//                "estimated_total_price" => 100,
//                "courier" => array(
//                    "name" => "Flash",
//                    "phone_number" => "925947647",
//                    "license_plate" => "AA-00-AA"
//                ),
//                "customer" => array(
//                    "name" => "Yurii",
//                    "phone_number" => "Mihali",
//                    "nif" => ""
//                ),
//                "products" => $newarray,
//                "obs" => '',
//                "orderIsAlreadyPaid" => false,
//                "payment_type" => $paymentType,
//                "delivery_address" => array(
//                    "label" => "Praceta do Município de São Domingos 2, 8400-415 Lagoa",
//                    "latitude" => "37.13637891839967",
//                    "longitude" => "-8.459192919573084"
//                ),
//                "is_picked_up_by_customer" => false,
//                "discounted_products_total" => 0,
//                "total_customer_to_pay" => 100
//            );
//
//            $payload = json_encode($payload);
//
//            $signature = hash_hmac('SHA256', $payload, $appsecret);
//
//            $ch = curl_init($url);
//            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
//            curl_setopt($ch, CURLOPT_POST, 1);
//            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
//            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//                'Content-Type: application/json',
//                'Authorization: '.$appkey,
//                'X-Integration-Signature: '.$signature
//            ));
//            $result = curl_exec($ch);
//            curl_close($ch);
//
//
//
//            $stateResult =  json_decode($result);
//            $stateOrder = $stateResult->Response->StatusMessage;
//
//
//            dd($stateOrder);





        return new JsonResponse([
            'status' => 1,
        ]);
    }


    /**
     * @Route("/ready-order", name="app_ready_order")
     */
    public function ReadyOrder(ManagerRegistry $doctrine, Request $request): Response
    {

        $orderid = $request->get('orderid');

        $en = $doctrine->getManager();

        $getOrderaData = $doctrine->getRepository(Apporders::class)->find($orderid);
            $getOrderaData->setOrderstate(2);
        $en->persist($getOrderaData);
        $en->flush();
        $getOrderList = $doctrine->getRepository(Apporderitems::class)->findBy(array('orderid' => $getOrderaData->getId()));
        $userdata = $doctrine->getRepository(Guestcontact::class)->find($getOrderaData->getUserid());
        $getclientAddress = $doctrine->getRepository(Guestcontactaddress::class)->findOneBy(array('idcontact'=> $userdata->getid()));
        $mail = new PHPMailer(true);
        $mail->CharSet = "UTF-8";
        try {
            //Server settings
            $mail->SMTPDebug  = SMTP::DEBUG_OFF;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'mail.pitombolanches.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = 'orders@pitombolanches.com';                     //SMTP username
            $mail->Password   = 'l%]u1jt)ko(y';                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('orders@pitombolanches.com', 'Pedidos - Pitombo Lanches');
            $mail->addAddress($userdata->getEmail(), $userdata->getName());     //Add a recipient
            $mail->addReplyTo('orders@pitombolanches.com', 'Duvidas');

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Encomenda Nr: '.$getOrderaData->getOrdernr().' Pitombo Lanches';



            $mail-> Body ='<html>
                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;border:0;max-width:600px">
        <tbody>
        <tr>
            <td valign="top" id="m_-7190579756368962722m_-1793210779212732005templateHeader" style="background-color:#262b32;background-image:none;background-repeat:no-repeat;background-position:center;background-size:cover;border-top:0;border-bottom:0;padding-top:9px;padding-bottom:0" bgcolor="#262b32">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;min-width:100%">
                    <tbody>
                    <tr>
                        <td valign="top">
                            <table align="left" border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse;max-width:100%;min-width:100%" width="100%">
                                <tbody>
                                <tr>
                                    <td valign="top" style="word-break:normal;font-size:16px;line-height:150%;padding:13px 13px 18px 25px;color:#ffffff;font-family:Arial,Helvetica Neue,Helvetica,sans-serif;text-align:center" align="center">
                                        <h1 style="display:block;margin:0;padding:0;color:#f8f8f8;font-family:\'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif;font-size:26px;font-style:normal;font-weight:normal;line-height:125%;letter-spacing:normal;text-align:center"><span class="il">Pitombo Lanches</span> - Pedido # '.$getOrderaData->getOrdernr().'</h1>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>';
            $mail -> Body .='
                         <tr>
            <td valign="top" id="m_-7190579756368962722m_-1793210779212732005templateBody" style="background-color:#ffffff;background-image:none;background-repeat:no-repeat;background-position:center;background-size:cover;border-top:0;border-bottom:2px solid #eaeaea;padding-top:0;padding-bottom:9px" bgcolor="#ffffff">
                <br>
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse">
                    <tbody>
                    <tr>
                        <td valign="top">
                            <table align="left" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse:collapse">
                                <tbody>
                                <tr>
                                    <td valign="top" style="word-break:normal;color:#606060;font-family:Arial;font-size:14px;line-height:150%;padding:9px 18px;text-align:left" align="left">
                                        <div>
                                       <span id="m_-7190579756368962722m_-1793210779212732005docs-internal-guid-6035f703-8c06-2131-54a9-a468a12bfaa8">
                                          <span style="background-color:transparent;font-family:arial;font-size:14px;vertical-align:baseline">
                                             <table style="border-collapse:collapse;width:100%" width="100%">
                                                <tbody>
                                                   <tr>
                                                      <td>
                                                         <div style="text-align:center">
                                                            ESTADO<br><strong>AGUARDA RECOLHA</strong>
                                                           
                                                         </div>
                                                      </td>
                                                      <td valign="top">
                                                         <div style="text-align:center">
                                                            Total<br>
                                                            <strong>'.$getOrderaData->getTotal().'&nbsp;€
                                                            
                                                            </strong>
                                                         </div>
                                                      </td>
                                                   </tr>
                                                </tbody>
                                             </table>
                                             <hr style="margin-top:18px;margin-bottom:18px;height:1px;border:0;border-top:1px solid #ccc">';

            $mail->Body .='
                                                               <table align="left" border="0" cellpadding="0" cellspacing="0" width="260" style="border-collapse:collapse">
                                                <tbody>
                                                   <tr>
                                                      <td style="padding-top:0px;padding-left:18px;padding-bottom:0px;padding-right:0">
                                                         <table border="0" cellpadding="9" cellspacing="0" width="100%" style="border-collapse:collapse">
                                                            <tbody>
                                                               <tr>
                                                                  <td valign="top" style="word-break:normal;color:#606060;font-family:Arial;font-size:14px;line-height:150%">
                                                                     <span style="line-height:150%">
                                                                     <strong style="line-height:20.7999992370605px">'.$userdata->getName().'</strong>
                                                                     <br>
                                                                     <strong><a href="tel:+351 934 869 755" style="color:#5ac15e;font-weight:normal;text-decoration:underline" rel="noreferrer" target="_blank">+351'.$userdata->getContact().'</a></strong>
                                                                     <br>
                                                                     <a href="mailto:'.$userdata->getEmail().'" rel="noreferrer" target="_blank">'.$userdata->getEmail().'</a>
                                                                     <br>';
            if (!empty($getclientAddress)){
                $mail->Body .= '<a href="https://maps.google.com/maps?q='.$getclientAddress->getLantitude().','.$getclientAddress->getLongitude().'">'.$getclientAddress->getStreet().'<br> '.$getclientAddress->getCity().'<br> '.$getclientAddress->getPostalcode().'</a>';
            }

            $mail->Body .= '</span>
                                                                     <br><br>
                                                                     <span style="line-height:150%">
                                                                     Observações: <strong style="white-space:pre-wrap">-</strong>
                                                                     </span>
                                                                  </td>
                                                               </tr>
                                                            </tbody>
                                                         </table>
                                                      </td>
                                                   </tr>
                                                </tbody>
                                             </table>';

            $mail->Body .='
                                                                     <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse">
                                                <tbody>
                                                   <tr>
                                                      <td valign="top">
                                                         <table align="left" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse:collapse">
                                                            <tbody>
                                                               <tr>
                                                                  <td style="padding-top:9px;padding-left:18px;padding-bottom:0px;padding-right:18px">
                                                                     <table style="border-collapse:collapse;border-bottom:1px solid #999999" cellpadding="0" cellspacing="0" width="100%">
                                                                        <tbody>
                                                                           <tr>
                                                                              <td valign="top">
                                                                                 <table width="100%" border="0" style="border-collapse:collapse;word-break:normal;color:#606060;font-family:Arial;font-size:14px;line-height:150%">
                                                                                    <tbody>
                                                                                       <tr style="line-height:110%">
                                                                                          <td style="text-align:left" nowrap="" align="left"><strong><span>Qtde</span></strong></td>
                                                                                          <td style="text-align:left" align="left"><strong><span>Itens</span></strong></td>
                                                                                          <td style="text-align:right" align="right"><strong><span>Preço (EUR)</span></strong></td>
                                                                                       </tr>';


            foreach ($getOrderList as $valueL) {
                $getOrderListExtraSize = $doctrine->getRepository(Foodmenuitemsmultiple::class)->find($valueL->getProductCode());

                $mail->Body .='<tr style="border-top:1px solid #999999;line-height:110%">
                                                                                          <td style="text-align:left" align="left"><span>'.$valueL->getQtd().'x</span></td>
                                                                                          <td style="text-align:left" align="left"><span><span class="il">'.$valueL->getItem().'</span></td>
                                                                                          <td style="text-align:right" align="right"><span>'.$valueL->getPrice().'&nbsp;€</span></td>
                                                                                       </tr>';



                foreach ($getOrderListExtraSize as $valueS){

                    $mail->Body .='
                                 <tr style="line-height:110%">
                                  <td>&nbsp;</td>
                                   <td>
                                  <span>Tamanho: <strong>'.$valueS->getitemname().' + '.$valueS->getitemprice().'</strong></span>
                                  </td>
                                  <td style="text-align:right" align="right">&nbsp;</td>
                                  </tr>
                ';

                }



            }


            $mail-> Body .='
                                                                            
                                                                         
                                                                                                       <tr style="border-top:1px solid #999999;line-height:110%">
                                                                                          <td>&nbsp;</td>
                                                                                          <td style="text-align:left" align="left"><span style="text-transform:uppercase">
                                                                                             Taxa de entrega  (23% incluído):
                                                                                             </span>
                                                                                          </td>
                                                                                          <td style="text-align:right" align="right"><span>5.00&nbsp;€</span></td>
                                                                                       </tr>
                                                                                       <tr style="line-height:110%">
                                                                                          <td>&nbsp;</td>
                                                                                          <td style="text-align:left" align="left"><span style="text-transform:uppercase">Bag</span></td>
                                                                                          <td style="text-align:right" align="right"><span>1.00&nbsp;€</span></td>
                                                                                       </tr>
                                                                      
                                                                                       <tr style="line-height:110%">
                                                                                          <td>&nbsp;</td>
                                                                                          <td style="text-align:left" align="left"><span style="text-transform:uppercase"><strong>Total</strong></span></td>
                                                                                          <td style="text-align:right" align="right"><span><strong>'.$getOrderaData->getTotal().'&nbsp;€</strong></span></td>
                                                                                       </tr>';




            $mail->Body .='                                                                                    </tbody>
                                                                                 </table>
                                                                              </td>
                                                                           </tr>
                                                                        </tbody>
                                                                     </table>
                                                                  </td>
                                                               </tr>
                                                            </tbody>
                                                         </table>
                                                      </td>
                                                   </tr>
                                                </tbody>
                                             </table>
                                             <p style="margin:10px 0;padding:0;color:#606060;font-family:Arial;font-size:14px;line-height:150%;text-align:center"><br>-</p>
                                          </span>
                                       </span>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>';


            $mail -> Body .='
                 <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse">
                    <tbody>
                    <tr>
                        <td valign="top">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;min-width:100%">
                                <tbody>
                                <tr>
                                    <td valign="top">
                                        <table align="left" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;min-width:100%">
                                            <tbody>
                                            <tr>
                                                <td style="padding-top:9px;padding-left:18px;padding-bottom:9px;padding-right:18px">
                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;min-width:100%;border:1px solid #dedede">
                                                        <tbody>
                                                        <tr>
                                                            <td valign="top">
                                                                <table align="left" border="0" cellpadding="0" cellspacing="0" width="310" style="border-collapse:collapse">
                                                                    <tbody>
                                                                    <tr>
                                                                        <td style="padding-top:0px;padding-right:18px;padding-bottom:0px;padding-left:0px">
                                                                            <table border="0" cellpadding="18" cellspacing="0" width="100%" style="border-collapse:collapse;min-width:100%">
                                                                                <tbody>
                                                                                <tr>
                                                                                    <td valign="top" style="word-break:normal;color:#606060;font-family:Arial;line-height:150%;font-size:12px;font-weight:normal;text-align:left" align="left">
                                                                                        <div style="text-align:left">Distribuído por:<br><a href="http://app.pitombolanches.com" style="color:#5ac15e;font-weight:normal;text-decoration:underline" rel="noreferrer" target="_blank" data-saferedirecturl="https://www.google.com/url?q=http://app.pitombolanches.com&amp;source=gmail&amp;ust=1663423092009000&amp;usg=AOvVaw2QnCYPUbCH5eaoXEBVv5mp"></a>
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                                </tbody>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                                <table align="left" border="0" cellpadding="0" cellspacing="0" width="252" style="border-collapse:collapse">
                                                                    <tbody>
                                                                    <tr>
                                                                        <td style="padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px">
                                                                            <table border="0" cellpadding="18" cellspacing="0" width="100%" style="border-collapse:collapse;min-width:100%">
                                                                                <tbody>
                                                                                <tr>
                                                                                    <td valign="top" style="word-break:normal;color:#606060;font-family:Arial;line-height:150%;font-size:12px;font-weight:normal;text-align:left" align="left">
                                                                                        <div style="width:212px"><a href="mailto:orders@pitombolanches.com" rel="noreferrer" target="_blank">orders@pitombolanches.com</a></div>
                                                                                        <span>Contacto: </span><a href="http://app.pitombolanches.com" style="font-weight:normal;text-decoration:underline;color:#5ac15e" rel="noreferrer" target="_blank" data-saferedirecturl="https://www.google.com/url?q=http://app.pitombolanches.com&amp;source=gmail&amp;ust=1663423092009000&amp;usg=AOvVaw2QnCYPUbCH5eaoXEBVv5mp">app.pitombolanches.com</a>
                                                                                    </td>
                                                                                </tr>
                                                                                </tbody>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr>
            <td valign="top" id="m_-7190579756368962722m_-1793210779212732005templateFooter" style="background-color:#f0efed;background-image:none;background-repeat:no-repeat;background-position:center;background-size:cover;border-top:0;border-bottom:0;padding-top:9px;padding-bottom:9px" bgcolor="#f0efed">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;min-width:100%">
                    <tbody>
                    <tr>
                        <td valign="top" style="padding-top:9px">
                            <table align="left" border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse;max-width:100%;min-width:100%" width="100%">
                                <tbody>
                                <tr>
                                    <td valign="top" style="word-break:normal;color:#656565;font-family:Helvetica;font-size:12px;line-height:150%;text-align:center;padding-top:0;padding-right:18px;padding-bottom:9px;padding-left:18px" align="center">
                                        Número ID: 208173
                                        |
                                        Nome: <span class="il">Pitombo Lanches</span> — Rua Dr. Manuel de Arriaga, edifício mar salgado, lote 1 loja E, 8365-140 ARMAÇÃO DE PÊRA
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        </tbody>
    </table> </html>';



//          $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
    $mail->send();
//          $session = $_COOKIE["session"];
//            $getdataCart = $doctrine->getRepository(Cart::class)->findAll();
//
//            foreach ($getdataCart as $valuscart){
//                $delorderextras = $doctrine->getRepository(Ordercartextras::class)->findBy(array('idorder' => $valuscart->getId()));
//                $delorderextrasm = $doctrine->getRepository(GridCart::class)->findBy(array('idorder' => $valuscart->getId()));
//                foreach ($delorderextras as $valdel1){
//                    $doctrine->getManager()->remove($valdel1);
//
//                }
//                foreach ($delorderextrasm as $valdel2){
//                    $doctrine->getManager()->remove($valdel2);
//
//                }
//                $doctrine->getManager()->remove($valuscart);
//                $doctrine->getManager()->flush();
//            }
//            $doctrine->getManager()->flush();

//
//
//            //ENVIO PARA ZONESOFT
//
//            $url = 'https://zsroi.zonesoft.org/v1.0/integration/order';
//
//            date_default_timezone_set("Europe/Lisbon");
//
//
//
//            $paymentType = "CASH";
//
////            if($paymentMethod == 1){
////                $paymentType = "CASH";
////            }else{
////                $paymentType = "Multibanco";
////            }
//
//
//            $en = $doctrine->getManager();
//
//
//            $tokendata = $en->getRepository(Zonesoftapi::class)->find(1);
//            $storeId = $tokendata->getStoreId();
//
//
//            $appsecret = $tokendata->getAppsecret();
//            $appkey = $tokendata->getAppkey();
//
//            $totalOrdered = 0;
//            $newarray = array();
//
//
//            $newarray[] = array(
//                'quantity' => 1,
//                'price' => 100,
//                'discount' => '0',
//                'name' => "Hamburger",
//                'id' => "121"
//            );
//
//
//            $order_id = rand(100,5000);
//            $order_id = "$order_id";
//
//            $payload = array(
//                "order_id" => $order_id,
//                "store_id" => $storeId,
//                "type_order" => "PICKUP",
////            "order_time" => $date=date("Y-m-d H:i:s"),
////            "estimated_pickup_time" => $date=date("Y-m-d H:i:s"),
//                "order_time" => "2020-11-09 12:24:53",
//                "estimated_pickup_time" => "2020-11-09 12:24:53",
//                "payment_method" => $paymentType,
//                "currency" => "EUR",
//                "delivery_fee" => 0,
//                "estimated_total_price" => 100,
//                "courier" => array(
//                    "name" => "Flash",
//                    "phone_number" => "925947647",
//                    "license_plate" => "AA-00-AA"
//                ),
//                "customer" => array(
//                    "name" => "Yurii",
//                    "phone_number" => "Mihali",
//                    "nif" => ""
//                ),
//                "products" => $newarray,
//                "obs" => '',
//                "orderIsAlreadyPaid" => false,
//                "payment_type" => $paymentType,
//                "delivery_address" => array(
//                    "label" => "Praceta do Município de São Domingos 2, 8400-415 Lagoa",
//                    "latitude" => "37.13637891839967",
//                    "longitude" => "-8.459192919573084"
//                ),
//                "is_picked_up_by_customer" => false,
//                "discounted_products_total" => 0,
//                "total_customer_to_pay" => 100
//            );
//
//            $payload = json_encode($payload);
//
//            $signature = hash_hmac('SHA256', $payload, $appsecret);
//
//            $ch = curl_init($url);
//            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
//            curl_setopt($ch, CURLOPT_POST, 1);
//            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
//            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//                'Content-Type: application/json',
//                'Authorization: '.$appkey,
//                'X-Integration-Signature: '.$signature
//            ));
//            $result = curl_exec($ch);
//            curl_close($ch);
//
//
//
//            $stateResult =  json_decode($result);
//            $stateOrder = $stateResult->Response->StatusMessage;
//
//
////            dd($stateOrder);

        } catch (Exception $e) {}



        return new JsonResponse();
    }


    /**
     * @Route("/pickup-order", name="app_pickup_order")
     */
    public function PickupOrder(ManagerRegistry $doctrine, Request $request): Response
    {

        $orderid = $request->get('orderid');
        $ordertype = $request->get('ordertype');

        $en = $doctrine->getManager();

        $getOrderaData = $doctrine->getRepository(Apporders::class)->find($orderid);
        $getOrderaData->setOrderstate(3);
        $en->persist($getOrderaData);
        $en->flush();
        $getOrderList = $doctrine->getRepository(Apporderitems::class)->findBy(array('orderid' => $getOrderaData->getId()));
        $userdata = $doctrine->getRepository(Guestcontact::class)->find($getOrderaData->getUserid());
        $getclientAddress = $doctrine->getRepository(Guestcontactaddress::class)->findOneBy(array('idcontact'=> $userdata->getid()));
        $mail = new PHPMailer(true);
        $mail->CharSet = "UTF-8";
        try {
            //Server settings
            $mail->SMTPDebug  = SMTP::DEBUG_OFF;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'mail.pitombolanches.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = 'orders@pitombolanches.com';                     //SMTP username
            $mail->Password   = 'l%]u1jt)ko(y';                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('orders@pitombolanches.com', 'Pedidos - Pitombo Lanches');
            $mail->addAddress($userdata->getEmail(), $userdata->getName());     //Add a recipient
            $mail->addReplyTo('orders@pitombolanches.com', 'Duvidas');

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Encomenda Nr: '.$getOrderaData->getOrdernr().' Pitombo Lanches';



            $mail-> Body ='<html>
                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;border:0;max-width:600px">
        <tbody>
        <tr>
            <td valign="top" id="m_-7190579756368962722m_-1793210779212732005templateHeader" style="background-color:#262b32;background-image:none;background-repeat:no-repeat;background-position:center;background-size:cover;border-top:0;border-bottom:0;padding-top:9px;padding-bottom:0" bgcolor="#262b32">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;min-width:100%">
                    <tbody>
                    <tr>
                        <td valign="top">
                            <table align="left" border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse;max-width:100%;min-width:100%" width="100%">
                                <tbody>
                                <tr>
                                    <td valign="top" style="word-break:normal;font-size:16px;line-height:150%;padding:13px 13px 18px 25px;color:#ffffff;font-family:Arial,Helvetica Neue,Helvetica,sans-serif;text-align:center" align="center">
                                        <h1 style="display:block;margin:0;padding:0;color:#f8f8f8;font-family:\'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif;font-size:26px;font-style:normal;font-weight:normal;line-height:125%;letter-spacing:normal;text-align:center"><span class="il">Pitombo Lanches</span> - Pedido # '.$getOrderaData->getOrdernr().'</h1>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>';


            if($ordertype == 'delivery'){
                $mail -> Body .='
                         <tr>
            <td valign="top" id="m_-7190579756368962722m_-1793210779212732005templateBody" style="background-color:#ffffff;background-image:none;background-repeat:no-repeat;background-position:center;background-size:cover;border-top:0;border-bottom:2px solid #eaeaea;padding-top:0;padding-bottom:9px" bgcolor="#ffffff">
                <br>
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse">
                    <tbody>
                    <tr>
                        <td valign="top">
                            <table align="left" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse:collapse">
                                <tbody>
                                <tr>
                                    <td valign="top" style="word-break:normal;color:#606060;font-family:Arial;font-size:14px;line-height:150%;padding:9px 18px;text-align:left" align="left">
                                        <div>
                                       <span id="m_-7190579756368962722m_-1793210779212732005docs-internal-guid-6035f703-8c06-2131-54a9-a468a12bfaa8">
                                          <span style="background-color:transparent;font-family:arial;font-size:14px;vertical-align:baseline">
                                             <table style="border-collapse:collapse;width:100%" width="100%">
                                                <tbody>
                                                   <tr>
                                                      <td>
                                                         <div style="text-align:center">
                                                            ESTADO<br><strong>A ENTREGAR</strong>
                                                           
                                                         </div>
                                                      </td>
                                                      <td valign="top">
                                                         <div style="text-align:center">
                                                            Total<br>
                                                            <strong>'.$getOrderaData->getTotal().'&nbsp;€
                                                            
                                                            </strong>
                                                         </div>
                                                      </td>
                                                   </tr>
                                                </tbody>
                                             </table>
                                             <hr style="margin-top:18px;margin-bottom:18px;height:1px;border:0;border-top:1px solid #ccc">';
            }else{
                $mail -> Body .='
                         <tr>
            <td valign="top" id="m_-7190579756368962722m_-1793210779212732005templateBody" style="background-color:#ffffff;background-image:none;background-repeat:no-repeat;background-position:center;background-size:cover;border-top:0;border-bottom:2px solid #eaeaea;padding-top:0;padding-bottom:9px" bgcolor="#ffffff">
                <br>
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse">
                    <tbody>
                    <tr>
                        <td valign="top">
                            <table align="left" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse:collapse">
                                <tbody>
                                <tr>
                                    <td valign="top" style="word-break:normal;color:#606060;font-family:Arial;font-size:14px;line-height:150%;padding:9px 18px;text-align:left" align="left">
                                        <div>
                                       <span id="m_-7190579756368962722m_-1793210779212732005docs-internal-guid-6035f703-8c06-2131-54a9-a468a12bfaa8">
                                          <span style="background-color:transparent;font-family:arial;font-size:14px;vertical-align:baseline">
                                             <table style="border-collapse:collapse;width:100%" width="100%">
                                                <tbody>
                                                   <tr>
                                                      <td>
                                                         <div style="text-align:center">
                                                            ESTADO<br><strong>PRONTO A RECOLHER</strong>
                                                           
                                                         </div>
                                                      </td>
                                                      <td valign="top">
                                                         <div style="text-align:center">
                                                            Total<br>
                                                            <strong>'.$getOrderaData->getTotal().'&nbsp;€
                                                            
                                                            </strong>
                                                         </div>
                                                      </td>
                                                   </tr>
                                                </tbody>
                                             </table>
                                             <hr style="margin-top:18px;margin-bottom:18px;height:1px;border:0;border-top:1px solid #ccc">';
            }



            $mail->Body .='
                                                               <table align="left" border="0" cellpadding="0" cellspacing="0" width="260" style="border-collapse:collapse">
                                                <tbody>
                                                   <tr>
                                                      <td style="padding-top:0px;padding-left:18px;padding-bottom:0px;padding-right:0">
                                                         <table border="0" cellpadding="9" cellspacing="0" width="100%" style="border-collapse:collapse">
                                                            <tbody>
                                                               <tr>
                                                                  <td valign="top" style="word-break:normal;color:#606060;font-family:Arial;font-size:14px;line-height:150%">
                                                                     <span style="line-height:150%">
                                                                     <strong style="line-height:20.7999992370605px">'.$userdata->getName().'</strong>
                                                                     <br>
                                                                     <strong><a href="tel:+351 934 869 755" style="color:#5ac15e;font-weight:normal;text-decoration:underline" rel="noreferrer" target="_blank">+351'.$userdata->getContact().'</a></strong>
                                                                     <br>
                                                                     <a href="mailto:'.$userdata->getEmail().'" rel="noreferrer" target="_blank">'.$userdata->getEmail().'</a>
                                                                     <br>';
            if (!empty($getclientAddress)){
                $mail->Body .= '<a href="https://maps.google.com/maps?q='.$getclientAddress->getLantitude().','.$getclientAddress->getLongitude().'">'.$getclientAddress->getStreet().'<br> '.$getclientAddress->getCity().'<br> '.$getclientAddress->getPostalcode().'</a>';
            }

                                                                    $mail->Body .='</span>
                                                                     <br><br>
                                                                     <span style="line-height:150%">
                                                                     Observações: <strong style="white-space:pre-wrap">-</strong>
                                                                     </span>
                                                                  </td>
                                                               </tr>
                                                            </tbody>
                                                         </table>
                                                      </td>
                                                   </tr>
                                                </tbody>
                                             </table>';

            $mail->Body .='
                                                                     <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse">
                                                <tbody>
                                                   <tr>
                                                      <td valign="top">
                                                         <table align="left" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse:collapse">
                                                            <tbody>
                                                               <tr>
                                                                  <td style="padding-top:9px;padding-left:18px;padding-bottom:0px;padding-right:18px">
                                                                     <table style="border-collapse:collapse;border-bottom:1px solid #999999" cellpadding="0" cellspacing="0" width="100%">
                                                                        <tbody>
                                                                           <tr>
                                                                              <td valign="top">
                                                                                 <table width="100%" border="0" style="border-collapse:collapse;word-break:normal;color:#606060;font-family:Arial;font-size:14px;line-height:150%">
                                                                                    <tbody>
                                                                                       <tr style="line-height:110%">
                                                                                          <td style="text-align:left" nowrap="" align="left"><strong><span>Qtde</span></strong></td>
                                                                                          <td style="text-align:left" align="left"><strong><span>Itens</span></strong></td>
                                                                                          <td style="text-align:right" align="right"><strong><span>Preço (EUR)</span></strong></td>
                                                                                       </tr>';



            foreach ($getOrderList as $valueL) {
                $getOrderListExtraSize = $doctrine->getRepository(Foodmenuitemsmultiple::class)->find($valueL->getProductCode());

                $mail->Body .='<tr style="border-top:1px solid #999999;line-height:110%">
                                                                                          <td style="text-align:left" align="left"><span>'.$valueL->getQtd().'x</span></td>
                                                                                          <td style="text-align:left" align="left"><span><span class="il">'.$valueL->getItem().'</span></td>
                                                                                          <td style="text-align:right" align="right"><span>'.$valueL->getPrice().'&nbsp;€</span></td>
                                                                                       </tr>';




                if (!is_null($getOrderListExtraSize)) {
                    foreach ($getOrderListExtraSize as $valueS){

                        $mail->Body .='
                                 <tr style="line-height:110%">
                                  <td>&nbsp;</td>
                                   <td>
                                  <span>Tamanho: <strong>'.$valueS->getitemname().' + '.$valueS->getitemprice().'</strong></span>
                                  </td>
                                  <td style="text-align:right" align="right">&nbsp;</td>
                                  </tr>
                ';

                    }
                }

            }


            $mail-> Body .='
                                                                            
                                                                         
                                                                                                       <tr style="border-top:1px solid #999999;line-height:110%">
                                                                                          <td>&nbsp;</td>
                                                                                          <td style="text-align:left" align="left"><span style="text-transform:uppercase">
                                                                                             Taxa de entrega  (23% incluído):
                                                                                             </span>
                                                                                          </td>
                                                                                          <td style="text-align:right" align="right"><span>5.00&nbsp;€</span></td>
                                                                                       </tr>
                                                                                       <tr style="line-height:110%">
                                                                                          <td>&nbsp;</td>
                                                                                          <td style="text-align:left" align="left"><span style="text-transform:uppercase">Bag</span></td>
                                                                                          <td style="text-align:right" align="right"><span>1.00&nbsp;€</span></td>
                                                                                       </tr>
                                                                      
                                                                                       <tr style="line-height:110%">
                                                                                          <td>&nbsp;</td>
                                                                                          <td style="text-align:left" align="left"><span style="text-transform:uppercase"><strong>Total</strong></span></td>
                                                                                          <td style="text-align:right" align="right"><span><strong>'.$getOrderaData->getTotal().'&nbsp;€</strong></span></td>
                                                                                       </tr>';




            $mail->Body .='                                                                                    </tbody>
                                                                                 </table>
                                                                              </td>
                                                                           </tr>
                                                                        </tbody>
                                                                     </table>
                                                                  </td>
                                                               </tr>
                                                            </tbody>
                                                         </table>
                                                      </td>
                                                   </tr>
                                                </tbody>
                                             </table>
                                             <p style="margin:10px 0;padding:0;color:#606060;font-family:Arial;font-size:14px;line-height:150%;text-align:center"><br>-</p>
                                          </span>
                                       </span>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>';


            $mail -> Body .='
                 <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse">
                    <tbody>
                    <tr>
                        <td valign="top">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;min-width:100%">
                                <tbody>
                                <tr>
                                    <td valign="top">
                                        <table align="left" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;min-width:100%">
                                            <tbody>
                                            <tr>
                                                <td style="padding-top:9px;padding-left:18px;padding-bottom:9px;padding-right:18px">
                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;min-width:100%;border:1px solid #dedede">
                                                        <tbody>
                                                        <tr>
                                                            <td valign="top">
                                                                <table align="left" border="0" cellpadding="0" cellspacing="0" width="310" style="border-collapse:collapse">
                                                                    <tbody>
                                                                    <tr>
                                                                        <td style="padding-top:0px;padding-right:18px;padding-bottom:0px;padding-left:0px">
                                                                            <table border="0" cellpadding="18" cellspacing="0" width="100%" style="border-collapse:collapse;min-width:100%">
                                                                                <tbody>
                                                                                <tr>
                                                                                    <td valign="top" style="word-break:normal;color:#606060;font-family:Arial;line-height:150%;font-size:12px;font-weight:normal;text-align:left" align="left">
                                                                                        <div style="text-align:left">Distribuído por:<br><a href="http://app.pitombolanches.com" style="color:#5ac15e;font-weight:normal;text-decoration:underline" rel="noreferrer" target="_blank" data-saferedirecturl="https://www.google.com/url?q=http://app.pitombolanches.com&amp;source=gmail&amp;ust=1663423092009000&amp;usg=AOvVaw2QnCYPUbCH5eaoXEBVv5mp"></a>
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                                </tbody>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                                <table align="left" border="0" cellpadding="0" cellspacing="0" width="252" style="border-collapse:collapse">
                                                                    <tbody>
                                                                    <tr>
                                                                        <td style="padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px">
                                                                            <table border="0" cellpadding="18" cellspacing="0" width="100%" style="border-collapse:collapse;min-width:100%">
                                                                                <tbody>
                                                                                <tr>
                                                                                    <td valign="top" style="word-break:normal;color:#606060;font-family:Arial;line-height:150%;font-size:12px;font-weight:normal;text-align:left" align="left">
                                                                                        <div style="width:212px"><a href="mailto:orders@pitombolanches.com" rel="noreferrer" target="_blank">orders@pitombolanches.com</a></div>
                                                                                        <span>Contacto: </span><a href="http://app.pitombolanches.com" style="font-weight:normal;text-decoration:underline;color:#5ac15e" rel="noreferrer" target="_blank" data-saferedirecturl="https://www.google.com/url?q=http://app.pitombolanches.com&amp;source=gmail&amp;ust=1663423092009000&amp;usg=AOvVaw2QnCYPUbCH5eaoXEBVv5mp">app.pitombolanches.com</a>
                                                                                    </td>
                                                                                </tr>
                                                                                </tbody>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr>
            <td valign="top" id="m_-7190579756368962722m_-1793210779212732005templateFooter" style="background-color:#f0efed;background-image:none;background-repeat:no-repeat;background-position:center;background-size:cover;border-top:0;border-bottom:0;padding-top:9px;padding-bottom:9px" bgcolor="#f0efed">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;min-width:100%">
                    <tbody>
                    <tr>
                        <td valign="top" style="padding-top:9px">
                            <table align="left" border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse;max-width:100%;min-width:100%" width="100%">
                                <tbody>
                                <tr>
                                    <td valign="top" style="word-break:normal;color:#656565;font-family:Helvetica;font-size:12px;line-height:150%;text-align:center;padding-top:0;padding-right:18px;padding-bottom:9px;padding-left:18px" align="center">
                                        Número ID: 208173
                                        |
                                        Nome: <span class="il">Pitombo Lanches</span> — Rua Dr. Manuel de Arriaga, edifício mar salgado, lote 1 loja E, 8365-140 ARMAÇÃO DE PÊRA
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        </tbody>
    </table> </html>';



//          $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
            $mail->send();
//          $session = $_COOKIE["session"];
//            $getdataCart = $doctrine->getRepository(Cart::class)->findAll();
//
//            foreach ($getdataCart as $valuscart){
//                $delorderextras = $doctrine->getRepository(Ordercartextras::class)->findBy(array('idorder' => $valuscart->getId()));
//                $delorderextrasm = $doctrine->getRepository(GridCart::class)->findBy(array('idorder' => $valuscart->getId()));
//                foreach ($delorderextras as $valdel1){
//                    $doctrine->getManager()->remove($valdel1);
//
//                }
//                foreach ($delorderextrasm as $valdel2){
//                    $doctrine->getManager()->remove($valdel2);
//
//                }
//                $doctrine->getManager()->remove($valuscart);
//                $doctrine->getManager()->flush();
//            }
//            $doctrine->getManager()->flush();



            //ENVIO PARA ZONESOFT

//            $url = 'https://zsroi.zonesoft.org/v1.0/integration/order';
//
//            date_default_timezone_set("Europe/Lisbon");
//
//
//
//            $paymentType = "CASH";
//
////            if($paymentMethod == 1){
////                $paymentType = "CASH";
////            }else{
////                $paymentType = "Multibanco";
////            }
//
//
//            $en = $doctrine->getManager();
//
//
//            $tokendata = $en->getRepository(Zonesoftapi::class)->find(1);
//            $storeId = $tokendata->getStoreId();
//
//
//            $appsecret = $tokendata->getAppsecret();
//            $appkey = $tokendata->getAppkey();
//
//            $totalOrdered = 0;
//            $newarray = array();
//
//
//            $newarray[] = array(
//                'quantity' => 1,
//                'price' => 100,
//                'discount' => '0',
//                'name' => "Hamburger",
//                'id' => "121"
//            );
//
//
//            $order_id = rand(100,5000);
//            $order_id = "$order_id";
//
//            $payload = array(
//                "order_id" => $order_id,
//                "store_id" => $storeId,
//                "type_order" => "PICKUP",
////            "order_time" => $date=date("Y-m-d H:i:s"),
////            "estimated_pickup_time" => $date=date("Y-m-d H:i:s"),
//                "order_time" => "2020-11-09 12:24:53",
//                "estimated_pickup_time" => "2020-11-09 12:24:53",
//                "payment_method" => $paymentType,
//                "currency" => "EUR",
//                "delivery_fee" => 0,
//                "estimated_total_price" => 100,
//                "courier" => array(
//                    "name" => "Flash",
//                    "phone_number" => "925947647",
//                    "license_plate" => "AA-00-AA"
//                ),
//                "customer" => array(
//                    "name" => "Yurii",
//                    "phone_number" => "Mihali",
//                    "nif" => ""
//                ),
//                "products" => $newarray,
//                "obs" => '',
//                "orderIsAlreadyPaid" => false,
//                "payment_type" => $paymentType,
//                "delivery_address" => array(
//                    "label" => "Praceta do Município de São Domingos 2, 8400-415 Lagoa",
//                    "latitude" => "37.13637891839967",
//                    "longitude" => "-8.459192919573084"
//                ),
//                "is_picked_up_by_customer" => false,
//                "discounted_products_total" => 0,
//                "total_customer_to_pay" => 100
//            );
//
//            $payload = json_encode($payload);
//
//            $signature = hash_hmac('SHA256', $payload, $appsecret);
//
//            $ch = curl_init($url);
//            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
//            curl_setopt($ch, CURLOPT_POST, 1);
//            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
//            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//                'Content-Type: application/json',
//                'Authorization: '.$appkey,
//                'X-Integration-Signature: '.$signature
//            ));
//            $result = curl_exec($ch);
//            curl_close($ch);
//
//
//
//            $stateResult =  json_decode($result);
//            $stateOrder = $stateResult->Response->StatusMessage;
//
//
//            dd($stateOrder);

        } catch (Exception $e) {}



        return new JsonResponse();
    }


    /**
     * @Route("/delivered-order", name="app_delivered_order")
     */
    public function deliveredOrder(ManagerRegistry $doctrine, Request $request): Response
    {

        $orderid = $request->get('orderid');
//
//        //ENVIO PARA ZONESOFT
//
//        $url = 'https://zsroi.zonesoft.org/v1.0/integration/order';
//
//        date_default_timezone_set("Europe/Lisbon");
//
//
//
//        $paymentMethod = 1;
//        if($paymentMethod == 1){
//            $paymentType = "CASH";
//        }else{
//            $paymentType = "Multibanco";
//        }
//
//
//        $en = $doctrine->getManager();
//
//
//        $tokendata = $en->getRepository(Zonesoftapi::class)->find(1);
//        $storeId = $tokendata->getStoreId();
//
//
//        $appsecret = $tokendata->getAppsecret();
//        $appkey = $tokendata->getAppkey();
//
//        $totalOrdered = 0;
//        $newarray = array();
//        $orderItems = $en->getRepository(Apporderitems::class)->findBy(['orderid' => $request->get('orderid')]);
////        $orderItems = $en->getRepository(Apporders::class)->findBy(['orderid' => $request->get('orderid')]);
////        $orderItems = $en->getRepository(Apporderitems::class)->findBy(['orderid' => $request->get('orderid')]);
////        $orderItems = $en->getRepository(Apporderitems::class)->findAll();
//
//
//        foreach ($orderItems as $prideold){
//
//            $totalOrdered = $totalOrdered + $prideold->getPrice();
//                $zoneSoftCode = $en->getRepository(Products::class)->findOneBy(['id' => $prideold->getProductCode()])->getZonesoftcode();
//
//            $newarray[] = array(
//                'quantity' => $prideold->getQtd(),
//                'price' => (bcmul($prideold->getPrice(),100)),
//                'discount' => '0',
//                'name' => $prideold->getItem(),
//                'id' => $zoneSoftCode
//            );
//        }
//
//        $payload = array(
//            "order_id" => $orderid,
//            "store_id" => $storeId,
//            "type_order" => "PICKUP",
//            "order_time" => $date=date("Y-m-d H:i:s"),
//            "estimated_pickup_time" => $date=date("Y-m-d H:i:s"),
//            "order_time" => "2020-11-09 12:24:53",
//            "estimated_pickup_time" => "2020-11-09 12:24:53",
//            "payment_method" => $paymentType,
//            "currency" => "EUR",
//            "delivery_fee" => 0,
//            "estimated_total_price" => 100,
//            "courier" => array(
//                "name" => "Flash",
//                "phone_number" => "925947647",
//                "license_plate" => "AA-00-AA"
//            ),
//            "customer" => array(
//                "name" => "Yurii",
//                "phone_number" => "Mihali",
//                "nif" => ""
//            ),
//            "products" => $newarray,
//            "obs" => '',
//            "orderIsAlreadyPaid" => false,
//            "payment_type" => $paymentType,
//            "delivery_address" => array(
//                "label" => "Praceta do Município de São Domingos 2, 8400-415 Lagoa",
//                "latitude" => "37.13637891839967",
//                "longitude" => "-8.459192919573084"
//            ),
//            "is_picked_up_by_customer" => false,
//            "discounted_products_total" => 0,
//            "total_customer_to_pay" => 100
//        );
//        $payload = json_encode($payload);
//
//        $signature = hash_hmac('SHA256', $payload, $appsecret);
//
//        $ch = curl_init($url);
//        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
//        curl_setopt($ch, CURLOPT_POST, 1);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//            'Content-Type: application/json',
//            'Authorization: '.$appkey,
//            'X-Integration-Signature: '.$signature
//        ));
//        $result = curl_exec($ch);
//        curl_close($ch);
//
//
//        $stateResult =  json_decode($result);
//
//        $stateOrder = $stateResult->Response->StatusMessage;

//        if ($stateResult->Response->StatusCode != 201) {
//
//            return new JsonResponse([
//                'status' => 0,
//                'orderid' => $order_id
//            ]);
//        } else

        $en = $doctrine->getManager();

        $getOrderaData = $doctrine->getRepository(Apporders::class)->find($orderid);
        $getOrderaData->setOrderstate(4);
        $en->persist($getOrderaData);
        $en->flush();

        $getOrderList = $doctrine->getRepository(Apporderitems::class)->findBy(array('orderid' => $getOrderaData->getId()));
        //Was changed to guestusers, because the normel users doesn't have access yet and don't work
        $userdata = $doctrine->getRepository(Guestcontact::class)->find($getOrderaData->getUserid());
        $getclientAddress = $doctrine->getRepository(Guestcontactaddress::class)->findOneBy(array('idcontact'=> $userdata ->getid()));
        $mail = new PHPMailer(true);
        $mail->CharSet = "UTF-8";
//        try {
        //Server settings
        $mail->SMTPDebug  = SMTP::DEBUG_OFF;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'mail.pitombolanches.com';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'orders@pitombolanches.com';                     //SMTP username
        $mail->Password   = 'l%]u1jt)ko(y';                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $mail->setFrom('orders@pitombolanches.com', 'Pedidos - Pitombo Lanches');
        $mail->addAddress($userdata->getEmail(), $userdata->getName());     //Add a recipient
        $mail->addReplyTo('orders@pitombolanches.com', 'Duvidas');

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Novo Estado Encomenda Nr: '.$getOrderaData->getOrdernr().' Pitombo Lanches';


            $mail-> Body ='<html>
                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;border:0;max-width:600px">
        <tbody>
        <tr>
            <td valign="top" id="m_-7190579756368962722m_-1793210779212732005templateHeader" style="background-color:#262b32;background-image:none;background-repeat:no-repeat;background-position:center;background-size:cover;border-top:0;border-bottom:0;padding-top:9px;padding-bottom:0" bgcolor="#262b32">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;min-width:100%">
                    <tbody>
                    <tr>
                        <td valign="top">
                            <table align="left" border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse;max-width:100%;min-width:100%" width="100%">
                                <tbody>
                                <tr>
                                    <td valign="top" style="word-break:normal;font-size:16px;line-height:150%;padding:13px 13px 18px 25px;color:#ffffff;font-family:Arial,Helvetica Neue,Helvetica,sans-serif;text-align:center" align="center">
                                        <h1 style="display:block;margin:0;padding:0;color:#f8f8f8;font-family:\'Helvetica Neue\',Helvetica,Arial,Verdana,sans-serif;font-size:26px;font-style:normal;font-weight:normal;line-height:125%;letter-spacing:normal;text-align:center"><span class="il">Pitombo Lanches</span> - Pedido # '.$getOrderaData->getOrdernr().'</h1>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>';
            $mail -> Body .='
                         <tr>
            <td valign="top" id="m_-7190579756368962722m_-1793210779212732005templateBody" style="background-color:#ffffff;background-image:none;background-repeat:no-repeat;background-position:center;background-size:cover;border-top:0;border-bottom:2px solid #eaeaea;padding-top:0;padding-bottom:9px" bgcolor="#ffffff">
                <br>
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse">
                    <tbody>
                    <tr>
                        <td valign="top">
                            <table align="left" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse:collapse">
                                <tbody>
                                <tr>
                                    <td valign="top" style="word-break:normal;color:#606060;font-family:Arial;font-size:14px;line-height:150%;padding:9px 18px;text-align:left" align="left">
                                        <div>
                                       <span id="m_-7190579756368962722m_-1793210779212732005docs-internal-guid-6035f703-8c06-2131-54a9-a468a12bfaa8">
                                          <span style="background-color:transparent;font-family:arial;font-size:14px;vertical-align:baseline">
                                             <table style="border-collapse:collapse;width:100%" width="100%">
                                                <tbody>
                                                   <tr>
                                                      <td>
                                                         <div style="text-align:center">
                                                            ESTADO<br><strong>ENTREGUE</strong>

                                                         </div>
                                                      </td>
                                                      <td valign="top">
                                                         <div style="text-align:center">
                                                            Total<br>
                                                            <strong>'.$getOrderaData->getTotal().'&nbsp;€

                                                            </strong>
                                                         </div>
                                                      </td>
                                                   </tr>
                                                </tbody>
                                             </table>
                                             <hr style="margin-top:18px;margin-bottom:18px;height:1px;border:0;border-top:1px solid #ccc">';

            $mail->Body .='
                                                               <table align="left" border="0" cellpadding="0" cellspacing="0" width="260" style="border-collapse:collapse">
                                                <tbody>
                                                   <tr>
                                                      <td style="padding-top:0px;padding-left:18px;padding-bottom:0px;padding-right:0">
                                                         <table border="0" cellpadding="9" cellspacing="0" width="100%" style="border-collapse:collapse">
                                                            <tbody>
                                                               <tr>
                                                                  <td valign="top" style="word-break:normal;color:#606060;font-family:Arial;font-size:14px;line-height:150%">
                                                                     <span style="line-height:150%">
                                                                     <strong style="line-height:20.7999992370605px">'.$userdata->getName().'</strong>
                                                                     <br>
                                                                     <strong><a href="tel:+351 934 869 755" style="color:#5ac15e;font-weight:normal;text-decoration:underline" rel="noreferrer" target="_blank">+351'.$userdata->getContact().'</a></strong>
                                                                     <br>
                                                                     <a href="mailto:'.$userdata->getEmail().'" rel="noreferrer" target="_blank">'.$userdata->getEmail().'</a>
                                                                     <br>
                                                                    
                                                                     ';
        if (!empty($getclientAddress)){
            $mail->Body .= '<a href="https://maps.google.com/maps?q='.$getclientAddress->getLantitude().','.$getclientAddress->getLongitude().'">'.$getclientAddress->getStreet().'<br> '.$getclientAddress->getCity().'<br> '.$getclientAddress->getPostalcode().'</a>';
        }

        $mail->Body .= '</span>
                                                                     <br><br>
                                                                     <span style="line-height:150%">
                                                                     Observações: <strong style="white-space:pre-wrap">-</strong>
                                                                     </span>
                                                                  </td>
                                                               </tr>
                                                            </tbody>
                                                         </table>
                                                      </td>
                                                   </tr>
                                                </tbody>
                                             </table>';

            $mail->Body .='
                                                                     <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse">
                                                <tbody>
                                                   <tr>
                                                      <td valign="top">
                                                         <table align="left" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse:collapse">
                                                            <tbody>
                                                               <tr>
                                                                  <td style="padding-top:9px;padding-left:18px;padding-bottom:0px;padding-right:18px">
                                                                     <table style="border-collapse:collapse;border-bottom:1px solid #999999" cellpadding="0" cellspacing="0" width="100%">
                                                                        <tbody>
                                                                           <tr>
                                                                              <td valign="top">
                                                                                 <table width="100%" border="0" style="border-collapse:collapse;word-break:normal;color:#606060;font-family:Arial;font-size:14px;line-height:150%">
                                                                                    <tbody>
                                                                                       <tr style="line-height:110%">
                                                                                          <td style="text-align:left" nowrap="" align="left"><strong><span>Qtde</span></strong></td>
                                                                                          <td style="text-align:left" align="left"><strong><span>Itens</span></strong></td>
                                                                                          <td style="text-align:right" align="right"><strong><span>Preço (EUR)</span></strong></td>
                                                                                       </tr>';



            foreach ($getOrderList as $valueL) {
                $getOrderListExtraSize = $doctrine->getRepository(Foodmenuitemsmultiple::class)->find($valueL->getProductCode());

                $mail->Body .='<tr style="border-top:1px solid #999999;line-height:110%">
                                                                                          <td style="text-align:left" align="left"><span>'.$valueL->getQtd().'x</span></td>
                                                                                          <td style="text-align:left" align="left"><span><span class="il">'.$valueL->getItem().'</span></td>
                                                                                          <td style="text-align:right" align="right"><span>'.$valueL->getPrice().'&nbsp;€</span></td>
                                                                                       </tr>';



                if (!is_null($getOrderListExtraSize)) {
                    foreach ($getOrderListExtraSize as $valueS) {

                        $mail->Body .= '
                                 <tr style="line-height:110%">
                                  <td>&nbsp;</td>
                                   <td>
                                  <span>Tamanho: <strong>' . $valueS->getitemname() . ' + ' . $valueS->getitemprice() . '</strong></span>
                                  </td>
                                  <td style="text-align:right" align="right">&nbsp;</td>
                                  </tr>
                ';

                    }

                }

            }


            $mail-> Body .='


                                                                                                       <tr style="border-top:1px solid #999999;line-height:110%">
                                                                                          <td>&nbsp;</td>
                                                                                          <td style="text-align:left" align="left"><span style="text-transform:uppercase">
                                                                                             Taxa de entrega  (23% incluído):
                                                                                             </span>
                                                                                          </td>
                                                                                          <td style="text-align:right" align="right"><span>5.00&nbsp;€</span></td>
                                                                                       </tr>
                                                                                       <tr style="line-height:110%">
                                                                                          <td>&nbsp;</td>
                                                                                          <td style="text-align:left" align="left"><span style="text-transform:uppercase">Bag</span></td>
                                                                                          <td style="text-align:right" align="right"><span>1.00&nbsp;€</span></td>
                                                                                       </tr>

                                                                                       <tr style="line-height:110%">
                                                                                          <td>&nbsp;</td>
                                                                                          <td style="text-align:left" align="left"><span style="text-transform:uppercase"><strong>Total</strong></span></td>
                                                                                          <td style="text-align:right" align="right"><span><strong>'.$getOrderaData->getTotal().'&nbsp;€</strong></span></td>
                                                                                       </tr>';




            $mail->Body .='                                                                                    </tbody>
                                                                                 </table>
                                                                              </td>
                                                                           </tr>
                                                                        </tbody>
                                                                     </table>
                                                                  </td>
                                                               </tr>
                                                            </tbody>
                                                         </table>
                                                      </td>
                                                   </tr>
                                                </tbody>
                                             </table>
                                             <p style="margin:10px 0;padding:0;color:#606060;font-family:Arial;font-size:14px;line-height:150%;text-align:center"><br>-</p>
                                          </span>
                                       </span>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>';


            $mail -> Body .='
                 <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse">
                    <tbody>
                    <tr>
                        <td valign="top">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;min-width:100%">
                                <tbody>
                                <tr>
                                    <td valign="top">
                                        <table align="left" border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;min-width:100%">
                                            <tbody>
                                            <tr>
                                                <td style="padding-top:9px;padding-left:18px;padding-bottom:9px;padding-right:18px">
                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;min-width:100%;border:1px solid #dedede">
                                                        <tbody>
                                                        <tr>
                                                            <td valign="top">
                                                                <table align="left" border="0" cellpadding="0" cellspacing="0" width="310" style="border-collapse:collapse">
                                                                    <tbody>
                                                                    <tr>
                                                                        <td style="padding-top:0px;padding-right:18px;padding-bottom:0px;padding-left:0px">
                                                                            <table border="0" cellpadding="18" cellspacing="0" width="100%" style="border-collapse:collapse;min-width:100%">
                                                                                <tbody>
                                                                                <tr>
                                                                                    <td valign="top" style="word-break:normal;color:#606060;font-family:Arial;line-height:150%;font-size:12px;font-weight:normal;text-align:left" align="left">
                                                                                        <div style="text-align:left">Distribuído por:<br><a href="http://app.pitombolanches.com" style="color:#5ac15e;font-weight:normal;text-decoration:underline" rel="noreferrer" target="_blank" data-saferedirecturl="https://www.google.com/url?q=http://app.pitombolanches.com&amp;source=gmail&amp;ust=1663423092009000&amp;usg=AOvVaw2QnCYPUbCH5eaoXEBVv5mp"></a>
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                                </tbody>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                                <table align="left" border="0" cellpadding="0" cellspacing="0" width="252" style="border-collapse:collapse">
                                                                    <tbody>
                                                                    <tr>
                                                                        <td style="padding-top:0px;padding-right:0px;padding-bottom:0px;padding-left:0px">
                                                                            <table border="0" cellpadding="18" cellspacing="0" width="100%" style="border-collapse:collapse;min-width:100%">
                                                                                <tbody>
                                                                                <tr>
                                                                                    <td valign="top" style="word-break:normal;color:#606060;font-family:Arial;line-height:150%;font-size:12px;font-weight:normal;text-align:left" align="left">
                                                                                        <div style="width:212px"><a href="mailto:orders@pitombolanches.com" rel="noreferrer" target="_blank">orders@pitombolanches.com</a></div>
                                                                                        <span>Contacto: </span><a href="http://app.pitombolanches.com" style="font-weight:normal;text-decoration:underline;color:#5ac15e" rel="noreferrer" target="_blank" data-saferedirecturl="https://www.google.com/url?q=http://app.pitombolanches.com&amp;source=gmail&amp;ust=1663423092009000&amp;usg=AOvVaw2QnCYPUbCH5eaoXEBVv5mp">app.pitombolanches.com</a>
                                                                                    </td>
                                                                                </tr>
                                                                                </tbody>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr>
            <td valign="top" id="m_-7190579756368962722m_-1793210779212732005templateFooter" style="background-color:#f0efed;background-image:none;background-repeat:no-repeat;background-position:center;background-size:cover;border-top:0;border-bottom:0;padding-top:9px;padding-bottom:9px" bgcolor="#f0efed">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;min-width:100%">
                    <tbody>
                    <tr>
                        <td valign="top" style="padding-top:9px">
                            <table align="left" border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse;max-width:100%;min-width:100%" width="100%">
                                <tbody>
                                <tr>
                                    <td valign="top" style="word-break:normal;color:#656565;font-family:Helvetica;font-size:12px;line-height:150%;text-align:center;padding-top:0;padding-right:18px;padding-bottom:9px;padding-left:18px" align="center">
                                        Número ID: 208173
                                        |
                                        Nome: <span class="il">Pitombo Lanches</span> — Rua Dr. Manuel de Arriaga, edifício mar salgado, lote 1 loja E, 8365-140 ARMAÇÃO DE PÊRA
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        </tbody>
    </table> </html>';


//          $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

//          $session = $_COOKIE["session"];
//            $getdataCart = $doctrine->getRepository(Cart::class)->findAll();
//
//            foreach ($getdataCart as $valuscart){
//                $delorderextras = $doctrine->getRepository(Ordercartextras::class)->findBy(array('idorder' => $valuscart->getId()));
//                $delorderextrasm = $doctrine->getRepository(GridCart::class)->findBy(array('idorder' => $valuscart->getId()));
//                foreach ($delorderextras as $valdel1){
//                    $doctrine->getManager()->remove($valdel1);
//
//                }
//                foreach ($delorderextrasm as $valdel2){
//                    $doctrine->getManager()->remove($valdel2);
//
//                }
//                $doctrine->getManager()->remove($valuscart);
//                $doctrine->getManager()->flush();
//            }
//            $doctrine->getManager()->flush();


//        } catch (Exception $e) {
//            dd('dasdadsda');
//
//        }

//
//
//            dd($stateResult);

        $mail->send();



        return new JsonResponse([
            'status' => 1,
            'orderid' => $orderid
        ]);
    }


}
