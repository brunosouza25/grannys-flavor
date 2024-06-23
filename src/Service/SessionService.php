<?php

namespace App\Service;


use Symfony\Component\HttpFoundation\RequestStack;


class SessionService
{
    private $guestContactAddressService;
    private $session;
    public function __construct(GuestContactAddressService $guestContactAddressService, RequestStack $requestStack)
    {
        $this->guestContactAddressService = $guestContactAddressService;
        $this->session = $requestStack->getSession();
    }

    public function checkSession()
    {
        if (is_null($this->session->get('session'))){
            $this->startSession();
            return $this->session->get('session');
        }

        return $this->session->get('session');
    }
    public function startSession()
    {
        function generateRandomString($length = 64) {
            return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
        }

        $sessionValue = generateRandomString();

        $this->setSession( $sessionValue);
        return $this->session->get('session');
    }
    public function setSession($session)
    {
        $this->session->set('session', $session);
    }

    public function destroySession()
    {
        $this->session->invalidate();
        return $this->session->get('session');
    }

}