<?php

namespace App\Entity;

use App\Repository\ProductsImageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductsImageRepository::class)
 */
class ProductsImage
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
    private $product_id;

    /**
     * @ORM\Column(type="string", length=511)
     */
    private $path;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $main_image;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function isMainImage(): ?bool
    {
        return $this->main_image;
    }

    public function setMainImage(?bool $main_image): self
    {
        $this->main_image = $main_image;

        return $this;
    }
}
