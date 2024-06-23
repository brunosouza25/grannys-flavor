<?php

namespace App\Entity;

use App\Repository\FoodadicionalconnectionitemmultipleRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FoodadicionalconnectionitemmultipleRepository::class)
 */
class Foodadicionalconnectionitemmultiple
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
    private $idcategory;

    /**
     * @ORM\Column(type="integer")
     */
    private $iditem;

    /**
     * @ORM\Column(type="integer")
     */
    private $iditemmultiple;

    /**
     * @ORM\Column(type="integer")
     */
    private $idadicional;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdcategory(): ?int
    {
        return $this->idcategory;
    }

    public function setIdcategory(int $idcategory): self
    {
        $this->idcategory = $idcategory;

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

    public function getIditemmultiple(): ?int
    {
        return $this->iditemmultiple;
    }

    public function setIditemmultiple(int $iditemmultiple): self
    {
        $this->iditemmultiple = $iditemmultiple;

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
