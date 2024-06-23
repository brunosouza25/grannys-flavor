<?php

namespace App\Service;

use App\Entity\OrderCart;
use App\Repository\OrderCartRepository;
use App\Repository\SystemConfigRepository;
use App\Repository\VouchersRepository;

class VouchersService
{

    private $vouchersRepository;
    private $orderCartService;
    private $orderCartRepository;

    public function __construct(VouchersRepository $vouchersRepository, OrderCartService $orderCartService, OrderCartRepository $orderCartRepository)
    {
        $this->vouchersRepository = $vouchersRepository;
        $this->orderCartService = $orderCartService;
        $this->orderCartRepository = $orderCartRepository;
    }

//    public function getVouchers()
//    {
//        return $this->vouchersRepository->findAll();
//    }
    public function getVouchers()
    {
        return $this->vouchersRepository->getVouchers();
    }

    public function newVoucher($voucherName, $voucherPorcentage, $voucherActive)
    {
        $date = new \DateTime(date('Y-m-d H:i:s'));

        $this->vouchersRepository->newVoucher($voucherName, $voucherPorcentage, $voucherActive, $date);
    }

    public function changeVoucherState($voucherId)
    {
        $this->vouchersRepository->changeVoucherState($voucherId);
    }

    public function checkVoucher($voucher)
    {
        $voucherChecked = $this->vouchersRepository->checkVoucher($voucher);
        if (empty($voucherChecked)) {
            return false;
        }
        return $voucherChecked[0];
    }

    public function checkVoucherById($voucherId)
    {
        $voucherChecked = $this->vouchersRepository->checkVoucherById($voucherId);
        if (empty($voucherChecked)) {
            return false;
        }
        return $voucherChecked[0];
    }

    public function applyVoucher($voucherId, $session)
    {
        $voucher = $this->checkVoucher($voucherId);

        if (!$voucher) {
            return false;
        }

        $orderCart = $this->orderCartService->checkSessionOrderCart($session);

        $this->orderCartRepository->linkVoucherToCart($orderCart['id'], $voucher['id']);

//        return
    }

    public function removeVoucher($orderCartId)
    {
        $this->orderCartRepository->linkVoucherToCart($orderCartId, null);
    }
}