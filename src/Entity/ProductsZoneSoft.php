<?php

namespace App\Entity;

use App\Repository\ProductsZoneSoftRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductsZoneSoftRepository::class)
 */
class ProductsZoneSoft
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"comment": "Chave estrangeira products->id"})
     */
    private $zone_soft_code;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $text;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $price1;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $price2;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $family_id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $sub_family_id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $option_id;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $state;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $image;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getPrice1(): ?float
    {
        return $this->price1;
    }

    public function setPrice1(?float $price1): self
    {
        $this->price1 = $price1;

        return $this;
    }

    public function getPrice2(): ?float
    {
        return $this->price2;
    }

    public function setPrice2(?float $price2): self
    {
        $this->price2 = $price2;

        return $this;
    }

    public function getFamilyId(): ?int
    {
        return $this->family_id;
    }

    public function setFamilyId(?int $family_id): self
    {
        $this->family_id = $family_id;

        return $this;
    }

    public function getZoneSoftCode(): ?int
    {
        return $this->zone_soft_code;
    }

    public function setZoneSoftCode(?int $zone_soft_code): self
    {
        $this->zone_soft_code = $zone_soft_code;

        return $this;
    }

    public function getSubFamilyId(): ?int
    {
        return $this->sub_family_id;
    }

    public function setSubFamilyId(?int $sub_family_id): self
    {
        $this->sub_family_id = $sub_family_id;

        return $this;
    }

    public function getOptionId(): ?int
    {
        return $this->option_id;
    }

    public function setOptionId(?int $option_id): self
    {
        $this->option_id = $option_id;

        return $this;
    }

    public function isState(): ?bool
    {
        return $this->state;
    }

    public function setState(?bool $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }
}
