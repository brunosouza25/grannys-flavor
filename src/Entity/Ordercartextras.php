<?php

namespace App\Entity;

use App\Repository\OrdercartextrasRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrdercartextrasRepository::class)
 */
class Ordercartextras
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
    private $idorder;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $price;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $qtd;

    /**
     * @ORM\Column(type="integer")
     */
    private $idadicional;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdorder(): ?int
    {
        return $this->idorder;
    }

    public function setIdorder(int $idorder): self
    {
        $this->idorder = $idorder;

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

    public function getQtd(): ?string
    {
        return $this->qtd;
    }

    public function setQtd(string $qtd): self
    {
        $this->qtd = $qtd;

        return $this;
    }

    public function getIdadicional(): ?int
    {
        return $this->idadicional;
    }

    public function setIdadicional(int $idadicional): self
    {
        $this->idadicional = $idadicional;

        return $this;
    }
}
