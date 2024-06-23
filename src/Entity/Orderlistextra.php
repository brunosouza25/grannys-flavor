<?php

namespace App\Entity;

use App\Repository\OrderlistextraRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderlistextraRepository::class)
 */
class Orderlistextra
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $extratype;

    /**
     * @ORM\Column(type="integer")
     */
    private $orderid;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $price;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $qtd;

    /**
     * @ORM\Column(type="integer")
     */
    private $idzonesoft;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExtratype(): ?string
    {
        return $this->extratype;
    }

    public function setExtratype(string $extratype): self
    {
        $this->extratype = $extratype;

        return $this;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function getQtd(): ?int
    {
        return $this->qtd;
    }

    public function setQtd(?int $qtd): self
    {
        $this->qtd = $qtd;

        return $this;
    }


    public function getIdZoneSoft(): ?int
    {
        return $this->idzonesoft;
    }

    public function setIdZoneSoft(int $idzonesoft): self
    {
        $this->idzonesoft = $idzonesoft;

        return $this;
    }


}
