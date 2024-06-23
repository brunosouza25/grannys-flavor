<?php

namespace App\Service;

use App\Entity\Guestcontact;

use App\Entity\Guestcontactaddress;
use App\Entity\MultibancoPayment;
use App\Entity\Orderlist;
use App\Entity\Products;
use App\Entity\SystemConfig;
use App\Repository\GuestcontactRepository;
use App\Repository\MultibancoPaymentRepository;
use App\Repository\OrderPaymentsRepository;
use App\Repository\PayByrdConfigRepository;
use App\Repository\StripeConfigRepository;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use GuzzleHttp\Client;
use Stripe\Charge;
use Stripe\Checkout\Session;
use Stripe\Service\ProductService;
use Stripe\Source;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\Response;

class PaymentService
{
    private $payByrdConfigRepository;
    private $stripeConfigRepository;
    private $contact;
    private $multibancoPaymentRepository;
    private $orderPaymentsRepository;
    private $orderService;
    private $systemConfigService;
    private $productService;
    private $emailService;

    public function __construct(PayByrdConfigRepository     $payByrdConfigRepository,
                                StripeConfigRepository      $stripeConfigRepository,
                                GuestContactService         $contact,
                                MultibancoPaymentRepository $multibancoPaymentRepository,
                                OrderPaymentsRepository     $orderPaymentsRepository,
                                OrderService                $orderService,
                                SystemConfigService         $systemConfigService,
                                ProductsService             $productService,
                                EmailService                $emailService)
    {
        $this->payByrdConfigRepository = $payByrdConfigRepository;
        $this->stripeConfigRepository = $stripeConfigRepository;
        $this->contact = $contact;
        $this->multibancoPaymentRepository = $multibancoPaymentRepository;
        $this->orderPaymentsRepository = $orderPaymentsRepository;
        $this->orderService = $orderService;
        $this->systemConfigService = $systemConfigService;
        $this->productService = $productService;
        $this->emailService = $emailService;
    }

    public function payByOrderSripe($oderReference, $amount)
    {
        $token = '';
        if ($_ENV['APP_VIVAWALLET'] == "dev") {
            $token = $this->getToken('dev_token');

        } else {
            $token = $this->getToken('token');

        }

        $stripePayment = new Stripe();
        $stripePayment->setApiKey($token);

        $user = $this->contact->getUserByOrderId($oderReference);

        $url = $_SERVER['HTTP_HOST'];

        $stripeCheckout = Session::create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'product_data' => ['name' => "Order: #$oderReference"],
                        'unit_amount' => bcmul($amount, 100),
                        'currency' => 'eur',
                    ],
                    'quantity' => 1,
                ],
            ],
            'success_url' => "https://$url/web/checkout/result?id=$oderReference",
            'cancel_url' => "https://$url",
            'customer_email' => $user['email'],
        ]);

        return $stripeCheckout->url;

    }

    public function payByMultiBanco($oderReference, $amount)
    {
        $token = '';
        if ($_ENV['APP_VIVAWALLET'] == "dev") {
            $token = $this->getToken('dev_token');

        } else {
            $token = $this->getToken('token');

        }

        $stripePayment = new Stripe();
        $stripePayment->setApiKey($token);

        $user = $this->contact->getUserByOrderId($oderReference);


//        $stripeCheckout = Source::create([
//            "type" => "multibanco",
//            "currency" => "eur",
//            "owner" => [
//                "email" => $user['email']
//            ],
//            "amount" => bcmul($amount, 100),
//        ]);

        //   return $stripeCheckout;

        $date = new DateTime();
        $date->modify('+7 days');
        $expiredate = $date->format('Ymd');
       //dd($expiredate);
//        $url = 'https://ifthenpay.com/api/gateway/paybylink/' . 'RBAY-069657';
        $url = 'https://ifthenpay.com/api/gateway/paybylink/' . 's';
        $payload = [

            'id' => $oderReference,
            'amount' => $amount,
            'expiredate' => $expiredate,
            'accounts' => 'PAYSHOP|DUF-268669;11989|718;MBWAY|KPZ-284433',
            'btnCloseUrl' => "https://dev.win-garden.com/",
            'btnCloseLabel' => "Fechar"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_GET, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode((object)$payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //dd($response);
        if ($statusCode === 200) {
            $content = json_decode($response, true);
            //dd($content);
            return $content;
        } else {
            throw new \Exception('Erro ao criar link de pagamento: ' . $response);
        }

    }

    public function getToken($token)
    {
        return $this->stripeConfigRepository->getToken($token);
    }

    public function changeMultibancoPaymentStatus($souceId, $status)
    {
        if ($status == 3) {
            $this->chargeMultibancoPayment($souceId);
        }
        $this->multibancoPaymentRepository->changeMultibancoPaymentStatus($souceId, $status);
    }

    public function chargeMultibancoPayment($souceId, ManagerRegistry $doctrine)
    {
        $token = '';
        if ($_ENV['APP_VIVAWALLET'] == "dev") {
            $token = $this->getToken('dev_token');

        } else {
            $token = $this->getToken('token');
        }

        $stripePayment = new Stripe();
        $stripePayment->setApiKey($token);

        $multiBancoPayment = $this->multibancoPaymentRepository->findOneBy(['source' => $souceId]);

        $charge = Charge::create([
            'amount' => bcmul($multiBancoPayment->getTotal(), 100),
            'currency' => 'eur',
            'source' => $souceId
        ]);

        if ($charge->status != 'succeeded') {
            return;
        }

        //Salvando o recibo
        $multiBancoPayment->setReceiptUrl($charge->receipt_url);

        //Alterado o status do multibancoayment para 4 que foi pago
        $this->changeMultibancoPaymentStatus($souceId, 4);

        //Buscando o order_payment
        $payment = $this->getPaymentById($multiBancoPayment->getPaymentId());

        //Alterado o order_payment para status 1 que foi pago
        $this->changePaymentStatus($payment->getId(), 1);

        $order = $this->orderService->getOrderById($payment->getOrderId());

        $orderProducts = $this->orderService->getOrderProducts($order->getId());

        $costumerInfo = $this->contact->getUserByOrderId($order->getId());

        $getclientAddress = $this->contact->getAddressById($order->getDeliveryAddressId());

        $systemConfig = $this->systemConfigService->getSystemConfig();

        //$fee = $systemConfig->getFixedFee();
        $fee = $this->contact->getFeeContact($doctrine);

        $newArray = [];

        foreach ($orderProducts as $product) {
            $productImage = $this->productService->getProductById($product->getProductId())->getImage();
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
        $email->setCostumerInfo(['destinationEmail' => $systemConfig->getEmailusername(), 'destinationName' => $systemConfig->getCompanyName()]);
        $email->setSubject("New order number: #" . $order->getId());
        $email->setEmailInfo();
        $email->saveInDataBase();
    }

    public function getPaymentById($paymentId)
    {
        return $this->orderPaymentsRepository->find($paymentId);
    }
    public function getPaymentByOrderId($paymentOrderId)
    {
        return $this->orderPaymentsRepository->findBy(['order_id' => $paymentOrderId]);
    }

    public function changePaymentStatus($paymentId, $status, $paymentInfo = null)
    {
        $this->orderPaymentsRepository->changePaymentStatus($paymentId, $status, $paymentInfo);
    }

    public function setPaymentStatus($payment)
    {
        $orderPayment = $this->getPaymentByOrderId($payment->id)[0];

        $this->changePaymentStatus($orderPayment->getId(), 1, $payment);

    }
}