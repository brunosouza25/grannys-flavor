<?php

namespace App\Service;

use App\Entity\Guestcontact;

use App\Repository\GuestcontactRepository;
use App\Repository\PayByrdConfigRepository;
use Doctrine\Persistence\ManagerRegistry;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Response;

class PayByrdService
{
    private $payByrdConfigRepository;
    public function __construct(PayByrdConfigRepository $payByrdConfigRepository)
    {
        $this->payByrdConfigRepository = $payByrdConfigRepository;
    }

    public function payByCard($card)
    {
        $client = new \GuzzleHttp\Client();

        $body = new \stdClass();

        $body->type = "Card";
        $body->acceptTokenization = false;
        $body->card = $card;
        $body->isPreAuth = false;
        $body->amount = "0.01";
        $body->currency = "EUR";
        $body->orderRef = "2";
        $body->brand = "Master";

        $url = 'https://gateway.paybyrd.com/api/v2/payment';

        $headers = array(
            'accept: application/json',
            'content-type: application/json',
            'x-api-key: 72f6a6bb-0b8c-4085-9369-66575025d977'
        );


        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);

        dd(json_decode($response));
    }

    public function getToken($token)
    {
        return $this->payByrdConfigRepository->getToken($token);
    }
    public function payByOrder($oderReference, $amount)
    {
        $token = '';
        if ($_ENV['APP_VIVAWALLET'] == "dev") {
                $token = $this->getToken('token_dev');

            } else {
                $token = $this->getToken('token');

            }
        $body = new \stdClass();
//        {"currency":"EUR","orderRef":"ABC123456","isoAmount":2500}',
        $amount = bcmul($amount, 100);;
        $body->isoAmount = $amount;
        $body->currency = "EUR";
        $body->orderRef = (integer)$oderReference;
        $body->orderOptions = ['redirectUrl' => "https://dev2.ryot.pt/web/checkout/result?id=$oderReference"];
        $body->shopper = ["email" => "brunopw25@gmail.com"];

        $url = 'https://gateway.paybyrd.com/api/v2/orders';

        $headers = array(
            'accept: application/json',
            'content-type: application/json',
            "x-api-key: $token"
        );



        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}