<?php

namespace App\Entity;

use App\Repository\OrderlistRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderlistRepository::class)
 */
class Orderlist
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
    private $orderid;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $item;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $qtd;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $price;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $extrainfo;

    /**
     * @ORM\Column(type="integer")
     */
    private $product_id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $product_grid_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderid(): ?int
    {
        return $this->orderid;
    }

    public function setOrderid(int $orderid): self
    {
        $this->orderid = $orderid;

        return $this;
    }

    public function getItem(): ?string
    {
        return $this->item;
    }

    public function setItem(string $item): self
    {
        $this->item = $item;

        return $this;
    }

    public function getQtd(): ?string
    {
        return $this->qtd;
    }

    public function setQtd(string $qtd): self
    {
        $this->qtd = $qtd;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getExtrainfo(): ?string
    {
        return $this->extrainfo;
    }

    public function setExtrainfo(string $extrainfo): self
    {
        $this->extrainfo = $extrainfo;

        return $this;
    }

    public function getProductId(): ?int
    {
        return $this->product_id;
    }

    public function setProductId(int $product_id): self
    {
        $this->product_id = $product_id;

        return $this;
    }

    public function getProductGridId(): ?int
    {
        return $this->product_grid_id;
    }

    public function setProductGridId(?int $product_grid_id): self
    {
        $this->product_grid_id = $product_grid_id;

        return $this;
    }
}
