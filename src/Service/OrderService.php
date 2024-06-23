<?php

namespace App\Service;

use App\Entity\Orderlist;
use App\Entity\Orders;
use App\Repository\OrderlistRepository;
use App\Repository\OrdersRepository;

class OrderService
{
    private $orderProductsRepository;
    private $ordersRepository;
    public function __construct(OrderlistRepository $orderProductsRepository, OrdersRepository $ordersRepository)
    {
        $this->orderProductsRepository = $orderProductsRepository;
        $this->ordersRepository = $ordersRepository;
    }


    public function getOrderProducts($orderId)
    {
        return $this->orderProductsRepository->getOrderProducts($orderId);
    }

    public function getOrderById($orderId)
    {
        return $this->ordersRepository->find($orderId);
    }
}