<?php

namespace App\Service;

use App\Repository\GuestcontactaddressRepository;
use App\Repository\GuestcontactRepository;
use function PHPUnit\Framework\isNull;


class GuestContactService
{
    private $guestContactRepository;
    private $sessionService;
    private $guestcontactaddressRepository;

    public function __construct(GuestcontactRepository $guestContactRepository, SessionService $sessionService, GuestcontactaddressRepository $guestcontactaddressRepository)
    {
        $this->guestContactRepository = $guestContactRepository;
        $this->sessionService = $sessionService;
        $this->guestcontactaddressRepository = $guestcontactaddressRepository;

    }
    public function getUserSession()
    {
        $user = $this->guestContactRepository->findOneBySomeField($this->sessionService->checkSession());
        return $user;

    }
    public function getUserByOrderId($orderId)
    {
        $user = $this->guestContactRepository->getUserByOrderId($orderId);
        return $user;

    }
    public function getUserById($userId)
    {
        $user = $this->guestContactRepository->find($userId);
        return $user;

    }

    public function getAddressById($addressId)
    {
        return $this->guestcontactaddressRepository->find($addressId);
    }
}