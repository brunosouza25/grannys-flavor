<?php

namespace App\Controller;

use App\Service\PayByrdService;
use App\Service\PaymentService;
use App\Service\StripeWebhookService;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/webhook")
 */
class WebhooksController extends AbstractController
{

    private $paymentService;
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * @Route("/stripe2")
     * rota teste
     */
    public function stripe2()
    {
        $this->paymentService->changeMultibancoPaymentStatus('src_1NmhUQKdobsuIEDsTAikMnSR', 3);

    }

    /**
     * @Route("/stripe")
     */
    public function stripe(Request $request): Response
    {
        $token = '';
        if ($_ENV['APP_VIVAWALLET'] == "dev") {
            $token = $this->paymentService->getToken('token_dev');

        } else {
            $token = $this->paymentService->getToken('token');

        }
        // Replace with your actual Stripe secret key
        $stripe = new StripeClient($token);

        // Replace with your actual webhook secret
        $endpointSecret = 'we_1NjuCkBzLczcGIdEprrvjZ5w';

        $payload = json_decode($request->getContent());

        switch ($payload->type) {
            case 'source.canceled':
                $sourceId = $payload->data->object->id;
                $this->paymentService->changeMultibancoPaymentStatus($sourceId, 0);
            case 'source.chargeable':
                $sourceId = $payload->data->object->id;
                $this->paymentService->changeMultibancoPaymentStatus($sourceId, 2);
            case 'source.failed':
                $sourceId = $payload->data->object->id;
                $this->paymentService->changeMultibancoPaymentStatus($sourceId, 3);

                break;
            // ... handle other event types
            default:
                return new Response('Received unknown event type ' . $payload->type, 200);
        }

        return new Response('', 200);
    }

    /**
     * @Route("/if_then_pay")
     */
    public function ifThenPay(Request $request): Response
    {
        $localKey = 'LEaUtclarBirADSTansPeTaTuALFiDEworGAUSitOReMedaYLE';

        $payment = new \stdClass();
        $payment->key = $request->get('key');
        $payment->id = $request->get('id');
        $payment->payment_datetime = $request->get('payment_datetime');

        if ($localKey != $payment->key){
            return new Response('', 401);
        }

        $this->paymentService->setPaymentStatus($payment);

        return new Response('', 200);
    }


}
