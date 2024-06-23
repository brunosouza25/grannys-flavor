<?php

namespace App\Entity;

use App\Repository\FoodmenuitemsmultipleRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FoodmenuitemsmultipleRepository::class)
 */
class Foodmenuitemsmultiple
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
    private $idfooditem;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $itemname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $itemprice;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $zonesoftcode;

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

    public function getIdfooditem(): ?int
    {
        return $this->idfooditem;
    }

    public function setIdfooditem(int $idfooditem): self
    {
        $this->idfooditem = $idfooditem;

        return $this;
    }

    public function getItemname(): ?string
    {
        return $this->itemname;
    }

    public function setItemname(string $itemname): self
    {
        $this->itemname = $itemname;

        return $this;
    }

    public function getItemprice(): ?string
    {
        return $this->itemprice;
    }

    public function setItemprice(?string $itemprice): self
    {
        $this->itemprice = $itemprice;

        return $this;
    }

    public function getZonesoftcode(): ?string
    {
        return $this->zonesoftcode;
    }

    public function setZonesoftcode(?string $zonesoftcode): self
    {
        $this->zonesoftcode = $zonesoftcode;

        return $this;
    }
}
