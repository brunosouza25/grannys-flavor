<?php

namespace App\Service;

use App\Entity\Guestcontact;

use App\Repository\CartRepository;
use App\Repository\GridRepository;
use App\Repository\GuestcontactaddressRepository;
use App\Repository\GuestcontactRepository;
use App\Repository\OrderCartRepository;


class OrderCartService
{
    private $cartRepository;
    private $orderCartRepository;
    public function __construct(CartRepository $cartRepository, OrderCartRepository $orderCartRepository)
    {
        $this->cartRepository = $cartRepository;
        $this->orderCartRepository = $orderCartRepository;

    }

    public function checkSessionOrderCart($session)
    {
        $orderCartCheck = $this->orderCartRepository->checkSessionOrderCart($session);

        if(!empty($orderCartCheck)){
            return $orderCartCheck[0];
        } else {
            $this->createOrderCart($session);
            return $this->orderCartRepository->checkSessionOrderCart($session)[0];
        }
    }

    public function createOrderCart($session)
    {
        $this->orderCartRepository->createOrderCart($session);

    }

    public function removeVoucher($orderCartId)
    {

        $this->orderCartRepository->removeVoucher($orderCartId);

    }
    public function deleteOrderCart($orderCartId)
    {
        $this->orderCartRepository->deleteOrderCart($orderCartId);
    }
    public function updateSession($oldSession, $newSession)
    {
        $orderCart = $this->checkSessionOrderCart($oldSession);
        $this->deleteOrderCart($orderCart['id']);
        $this->orderCartRepository->updateSession($oldSession, $newSession);
    }

}