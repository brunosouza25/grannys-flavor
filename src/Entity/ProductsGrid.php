<?php

namespace App\Entity;

use App\Repository\ProductsGridRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductsGridRepository::class)
 */
class ProductsGrid
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
     * @ORM\Column(type="integer", nullable=true)
     */
    private $grid_color_id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $grid_size_id;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $code;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $stock;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $update_status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $last_update;

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

    public function setGridId(int $grid_id): self
    {
        $this->grid_id = $grid_id;

        return $this;
    }

    public function setGridColorId(?int $grid_color_id): self
    {
        $this->grid_color_id = $grid_color_id;

        return $this;
    }

    public function getGridColorId(): ?int
    {
        return $this->grid_color_id;
    }

    public function getGridSizeId(): ?int
    {
        return $this->grid_size_id;
    }

    public function setGridSizeId(?int $grid_size_id): self
    {
        $this->grid_size_id = $grid_size_id;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(?bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(?int $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    public function getUpdateStatus(): ?int
    {
        return $this->update_status;
    }

    public function setUpdateStatus(?int $update_status): self
    {
        $this->update_status = $update_status;

        return $this;
    }

    public function getLastUpdate(): ?\DateTimeInterface
    {
        return $this->last_update;
    }

    public function setLastUpdate(?\DateTimeInterface $last_update): self
    {
        $this->last_update = $last_update;

        return $this;
    }

}
