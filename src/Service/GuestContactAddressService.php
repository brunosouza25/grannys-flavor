<?php

namespace App\Service;

use App\Entity\Guestcontact;

use App\Repository\CartRepository;
use App\Repository\GridRepository;
use App\Repository\GuestcontactaddressRepository;
use App\Repository\GuestcontactRepository;


class GuestContactAddressService
{
    private $guestContactAddressRepository;
    public function __construct(GuestcontactaddressRepository $guestContactAddressRepository)
    {
        $this->guestContactAddressRepository = $guestContactAddressRepository;
    }

}