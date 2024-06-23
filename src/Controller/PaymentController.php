<?php

namespace App\Controller;

use App\Service\IfThenPayService;
use App\Service\PayByrdService;
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
     * @Route("/payment_by_ifthenpay", name="payment_by_ifthenpay")
     */
    public function createLinkPaymentIfThenPay(Request $request): Response
    {
//        $id = '';
//        $amount = '21.50';
        //dd($request);

        $id = $request->get('id');
        $amount = $request->get('amount');

        try {
            $urlPagamento = $this->ifthenpayService->createLinkPayment($id, $amount);
            return $this->json(['urlPagamento' => $urlPagamento]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
