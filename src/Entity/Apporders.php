<?php

namespace App\Entity;

use App\Repository\AppordersRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AppordersRepository::class)
 */
class Apporders
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $userid;

    /**
     * @ORM\Column(type="integer")
     */
    private $orderstate;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $total;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $paymenttype;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $dateorder;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $timeorder;

    /**
     * @ORM\Column(type="integer")
     */
    private $paymentstatus;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ordercodevw;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ordernr;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ordertype;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $timeprepare;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserid(): ?int
    {
        return $this->userid;
    }

    public function setUserid(int $userid): self
    {
        $this->userid = $userid;

        return $this;
    }

    public function getOrderstate(): ?int
    {
        return $this->orderstate;
    }

    public function setOrderstate(int $orderstate): self
    {
        $this->orderstate = $orderstate;

        return $this;
    }

    public function getTotal(): ?string
    {
        return $this->total;
    }

    public function setTotal(string $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getPaymenttype(): ?string
    {
        return $this->paymenttype;
    }

    public function setPaymenttype(string $paymenttype): self
    {
        $this->paymenttype = $paymenttype;

        return $this;
    }

    public function getDateorder(): ?string
    {
        return $this->dateorder;
    }

    public function setDateorder(string $dateorder): self
    {
        $this->dateorder = $dateorder;

        return $this;
    }

    public function getTimeorder(): ?string
    {
        return $this->timeorder;
    }

    public function setTimeorder(string $timeorder): self
    {
        $this->timeorder = $timeorder;

        return $this;
    }

    public function getPaymentstatus(): ?int
    {
        return $this->paymentstatus;
    }

    public function setPaymentstatus(int $paymentstatus): self
    {
        $this->paymentstatus = $paymentstatus;

        return $this;
    }

    public function getOrdercodevw(): ?string
    {
        return $this->ordercodevw;
    }

    public function setOrdercodevw(?string $ordercodevw): self
    {
        $this->ordercodevw = $ordercodevw;

        return $this;
    }

    public function getOrdernr(): ?string
    {
        return $this->ordernr;
    }

    public function setOrdernr(string $ordernr): self
    {
        $this->ordernr = $ordernr;

        return $this;
    }

    public function getOrdertype(): ?string
    {
        return $this->ordertype;
    }

    public function setOrdertype(string $ordertype): self
    {
        $this->ordertype = $ordertype;

        return $this;
    }

    public function getTimeprepare(): ?string
    {
        return $this->timeprepare;
    }

    public function setTimeprepare(string $timeprepare): self
    {
        $this->timeprepare = $timeprepare;

        return $this;
    }
}
