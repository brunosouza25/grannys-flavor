<?php

namespace App\Controller;

use App\Entity\MultibancoPayment;
use App\Entity\StripeConfig;
use App\Service\IfThenPayService;
use App\Service\PayByrdService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/payment", name="payment")
 */
class PaymentController extends AbstractController
{
    private $payByrdService;
    private $ifthenpayService;
    public function __construct(PayByrdService $payByrdService, IfthenpayService $ifthenpayService)
    {
        $this->payByrdService = $payByrdService;
        $this->ifthenpayService = $ifthenpayService;
    }

    /**
     * @Route("/payment_by_card", name="payment_by_card")
     */
    public function index(Request $request): Response
    {
        $card = new \stdClass();

        $card->holder = $request->get('costumerName');
        $card->expiration = $request->get('expiration');
        $card->number = $request->get('cardNumber');
        $card->cvv = $request->get('cardCcv');
//        $this->payByrdService->payByCard($card);

        return new JsonResponse($this->payByrdService->payByCard($card));

    }

    /**
     * @Route("/payment_page", name="payment_page")
     */
    public function paymentPage(Request $request, ManagerRegistry $doctrine): Response
    {
        $stripeConfig = $doctrine->getManager()->getRepository(StripeConfig::class)->find(1);
        if ($_ENV['APP_ENV'] == 'dev') {
            $token = $stripeConfig->getDevPublicToken();
        } else {
            $token = $stripeConfig->getPublicKey();

        }

        $clientSecret = $request->get('clientSecret');
        return $this->render('ordering/buy2.html.twig', [
            'clientSecret' => $clientSecret,
            'token' => $token,
        ]);

    }

    /**
     * @Route("/get_payment_intent", name="get_payment_intent")
     */
    public function getPaymentIntent(Request $request)
    {



        $stripe = new \Stripe\StripeClient('sk_test_51PVkqZ00r9NE2VfE3PaSfHa5wrwTiMIsqqhgiOYoZyWvI4Rh2mCb1rIIK2YmnuMMaJpUPbur1sq4BxTXGR7QDlnz00J443MgDE');
       $a = $stripe->paymentIntents->retrieve('pi_3PW4Xl00r9NE2VfE1TSFca7y', []);

            dd($a);
    }

    /**
     * @Route("/set_multibanco_info", name="set_multibanco_info")
     */
    public function setMultibancoInfo(Request $request, ManagerRegistry $doctrine)
    {
        $multiBancoId =  $request->get('multiBancoId');

        $multiBancoPayment = $doctrine->getRepository(MultibancoPayment::class)->findOneBy(['source'=> $multiBancoId]);

        $multiBancoPayment->setEntity($request->get('entity'));
        $multiBancoPayment->setReference($request->get('reference'));
        $multiBancoPayment->setPaymentUrl($request->get('url'));

        $en = $doctrine->getManager();
        $en->flush();
        return new Response();
    }


}
