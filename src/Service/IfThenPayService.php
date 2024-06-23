<?php

namespace App\Service;

use App\Entity\Guestcontact;

use App\Repository\GuestcontactRepository;
use App\Repository\PayByrdConfigRepository;
use Doctrine\Persistence\ManagerRegistry;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Response;

class IfThenPayService
{
    private $gatewayKey;

    public function __construct()
    {
        $this->gatewayKey = "RBAY-069657";
    }

    public function createLinkPayment($id, $amount)
    {
        $url = 'https://ifthenpay.com/api/gateway/paybylink/' . $this->gatewayKey;
        //dd($id);
        $payload = [
            'id' => $id,
            'amount' => $amount,
            'expiredate' => '20240322'
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

        if ($statusCode === 200) {
            $content = json_decode($response, true);
            return $content['url'];
        } else {
            throw new \Exception('Erro ao criar link de pagamento: ' . $response);
        }
    }


}