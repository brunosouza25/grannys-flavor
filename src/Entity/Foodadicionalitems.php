<?php

namespace App\Entity;

use App\Repository\FoodadicionalitemsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FoodadicionalitemsRepository::class)
 */
class Foodadicionalitems
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
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $price;

    /**
     * @ORM\Column(type="integer")
     */
    private $idadicional;

    /**
     * @ORM\Column(type="integer")
     */
    private $preselected;

    /**
     * @ORM\Column(type="integer")
     */
    private $idzonesoft;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getIdadicional(): ?int
    {
        return $this->idadicional;
    }

    public function setIdadicional(int $idadicional): self
    {
        $this->idadicional = $idadicional;

        return $this;
    }

    public function getPreselected(): ?int
    {
        return $this->preselected;
    }

    public function setPreselected(int $preselected): self
    {
        $this->preselected = $preselected;

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
