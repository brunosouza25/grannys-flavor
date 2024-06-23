<?php

namespace App\Entity;

use App\Repository\OrderCartRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderCartRepository::class)
 */
class OrderCart
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=512)
     */
    private $session;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $voucher_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSession(): ?string
    {
        return $this->session;
    }

    public function setSession(string $session): self
    {
        $this->session = $session;

        return $this;
    }

    public function getVoucherId(): ?int
    {
        return $this->voucher_id;
    }

    public function setVoucherId(?int $voucher_id): self
    {
        $this->voucher_id = $voucher_id;

        return $this;
    }
}
