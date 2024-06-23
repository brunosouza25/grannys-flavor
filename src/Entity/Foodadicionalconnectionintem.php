<?php

namespace App\Entity;

use App\Repository\FoodadicionalconnectionintemRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FoodadicionalconnectionintemRepository::class)
 */
class Foodadicionalconnectionintem
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
    private $idadicional;

    /**
     * @ORM\Column(type="integer")
     */
    private $iditem;

    /**
     * @ORM\Column(type="integer")
     */
    private $idcategoryfood;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getIditem(): ?int
    {
        return $this->iditem;
    }

    public function setIditem(int $iditem): self
    {
        $this->iditem = $iditem;

        return $this;
    }

    public function getIdcategoryfood(): ?int
    {
        return $this->idcategoryfood;
    }

    public function setIdcategoryfood(int $idcategoryfood): self
    {
        $this->idcategoryfood = $idcategoryfood;

        return $this;
    }
}
