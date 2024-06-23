<?php

namespace App\Entity;

use App\Repository\OrdersRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrdersRepository::class)
 */
class Orders
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
    private $user_id;

    /**
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $value;

    /**
     * @ORM\Column(type="integer")
     */
    private $paymenttype;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $time;

    /**
     * @ORM\Column(type="string", length=1024)
     */
    private $orderCodeVW;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ordertype;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $cancellation_time;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    private $cancellation_observation;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $voucher;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nif;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nif_email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nif_name;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $invoicing_address_id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $delivery_address_id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $zone_soft_order_id;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $user_comment;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $admin_comment;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ordernr;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $email_sending_status;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $zone_soft_sending;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $voucher_discount;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserid(): ?int
    {
        return $this->user_id;
    }

    public function setUserid(int $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

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

    public function getTime(): ?string
    {
        return $this->time;
    }

    public function setTime(string $time): self
    {
        $this->time = $time;

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

    public function getOrderCodeVW(): ?string
    {
        return $this->orderCodeVW;
    }

    public function setOrderCodeVW(string $orderCodeVW): self
    {
        $this->orderCodeVW = $orderCodeVW;

        return $this;
    }

    public function getOrderNr(): ?string
    {
        return $this->orderNr;
    }

    public function setOrderNr(string $orderNr): self
    {
        $this->orderNr = $orderNr;

        return $this;
    }

    public function getOrdertype(): ?string
    {
        return $this->ordertype;
    }

    public function setOrdertype(?string $ordertype): self
    {
        $this->ordertype = $ordertype;

        return $this;
    }

    //ja vejo para deletar
    public function setOrdertyme(?string $ordertyme): self
    {
        $this->ordertyme = $ordertyme;

        return $this;
    }

    public function getCancellationTime(): ?\DateTimeInterface
    {
        return $this->cancellation_time;
    }

    public function setCancellationTime(?\DateTimeInterface $cancellation_time): self
    {
        $this->cancellation_time = $cancellation_time;

        return $this;
    }

    public function getCancellationObservation(): ?string
    {
        return $this->cancellation_observation;
    }

    public function setCancellationObservation(?string $cancellation_observation): self
    {
        $this->cancellation_observation = $cancellation_observation;

        return $this;
    }

    public function getVoucher(): ?string
    {
        return $this->voucher;
    }

    public function setVoucher(?string $voucher): self
    {
        $this->voucher = $voucher;

        return $this;
    }

    public function getNif(): ?string
    {
        return $this->nif;
    }

    public function setNif(?string $nif): self
    {
        $this->nif = $nif;

        return $this;
    }

    public function getNifEmail(): ?string
    {
        return $this->nif_email;
    }

    public function setNifEmail(?string $nif_email): self
    {
        $this->nif_email = $nif_email;

        return $this;
    }

    public function getNifName(): ?string
    {
        return $this->nif_name;
    }

    public function setNifName(?string $nif_name): self
    {
        $this->nif_name = $nif_name;

        return $this;
    }

    public function getInvoicingAddressId(): ?int
    {
        return $this->invoicing_address_id;
    }

    public function setInvoicingAddressId(?int $invoicing_address_id): self
    {
        $this->invoicing_address_id = $invoicing_address_id;

        return $this;
    }

    public function getDeliveryAddressId(): ?int
    {
        return $this->delivery_address_id;
    }

    public function setDeliveryAddressId(?int $delivery_address_id): self
    {
        $this->delivery_address_id = $delivery_address_id;

        return $this;
    }

    public function getZoneSoftOrderId(): ?string
    {
        return $this->zone_soft_order_id;
    }

    public function setZoneSoftOrderId(?string $zone_soft_order_id): self
    {
        $this->zone_soft_order_id = $zone_soft_order_id;

        return $this;
    }

    public function isUserComment(): ?bool
    {
        return $this->user_comment;
    }

    public function setUserComment(?bool $user_comment): self
    {
        $this->user_comment = $user_comment;

        return $this;
    }

    public function isAdminComment(): ?bool
    {
        return $this->admin_comment;
    }

    public function setAdminComment(?bool $admin_comment): self
    {
        $this->admin_comment = $admin_comment;

        return $this;
    }

    public function getEmailSendingStatus(): ?int
    {
        return $this->email_sending_status;
    }

    public function setEmailSendingStatus(?int $email_sending_status): self
    {
        $this->email_sending_status = $email_sending_status;

        return $this;
    }

    public function isZoneSoftSending(): ?bool
    {
        return $this->zone_soft_sending;
    }

    public function setZoneSoftSending(?bool $zone_soft_sending): self
    {
        $this->zone_soft_sending = $zone_soft_sending;

        return $this;
    }

    public function getVoucherDiscount(): ?float
    {
        return $this->voucher_discount;
    }

    public function setVoucherDiscount(?float $voucher_discount): self
    {
        $this->voucher_discount = $voucher_discount;

        return $this;
    }
}
