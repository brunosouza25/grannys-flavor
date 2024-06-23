<?php

namespace App\Service;

use App\Entity\Guestcontact;

use App\Repository\CartRepository;
use App\Repository\GridRepository;
use App\Repository\GuestcontactaddressRepository;
use App\Repository\GuestcontactRepository;


class CartService
{
    private $cartRepository;
    private $sessionService;
    public function __construct(CartRepository $cartRepository, SessionService $sessionService)
    {
        $this->cartRepository = $cartRepository;
        $this->sessionService = $sessionService;

    }

    public function getCartQuantity()
    {
        $session = $this->sessionService->checkSession();
        return $this->cartRepository->getCartQuantity($session);
    }

    public function updateCartQuantity($quantity, $cardId)
    {
        return $this->cartRepository->updateCartQuantity($quantity, $cardId);
    }
}