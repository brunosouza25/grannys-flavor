<?php

namespace App\Entity;

use App\Repository\MultibancoPaymentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MultibancoPaymentRepository::class)
 */
class MultibancoPayment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $source;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $entity;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $reference;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $total;

    /**
     * @ORM\Column(type="integer")
     */
    private $payment_id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    private $receipt_url;

    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    private $payment_url;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    private $client_secret;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getEntity(): ?string
    {
        return $this->entity;
    }

    public function setEntity(?string $entity): self
    {
        $this->entity = $entity;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(?float $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getPaymentId(): ?int
    {
        return $this->payment_id;
    }

    public function setPaymentId(int $payment_id): self
    {
        $this->payment_id = $payment_id;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getReceiptUrl(): ?string
    {
        return $this->receipt_url;
    }

    public function setReceiptUrl(?string $receipt_url): self
    {
        $this->receipt_url = $receipt_url;

        return $this;
    }

    public function getPaymentUrl(): ?string
    {
        return $this->payment_url;
    }

    public function setPaymentUrl(?string $payment_url): self
    {
        $this->payment_url = $payment_url;

        return $this;
    }

    public function getClientSecret(): ?string
    {
        return $this->client_secret;
    }

    public function setClientSecret(?string $client_secret): self
    {
        $this->client_secret = $client_secret;

        return $this;
    }

}
