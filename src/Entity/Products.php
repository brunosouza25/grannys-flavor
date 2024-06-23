<?php

namespace App\Entity;

use App\Repository\ProductsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductsRepository::class)
 */
class Products
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"comment": "Chave estrangeira category->id"})
     */
    private $category_id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="float", length=255, nullable=true)
     */
    private $price;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="boolean")
     */
    private $state;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $menustate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ordernr;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $zone_soft_code;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $timein;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $timeout;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $timeinm;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $timeoutm;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $highlight;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $brand_id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $material_id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $bore_id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $type_id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $model_id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $tag_id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $code;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $grid;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $deleted;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $iva;

    /**
     * @ORM\Column(type="integer")
     */
    private $stock;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $sub_family_id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $update_status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $last_update;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $equipament_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategoryId(): ?int
    {
        return $this->category_id;
    }

    public function setCategoryId(int $category_id): self
    {
        $this->category_id = $category_id;

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

    public function setPrice(?string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(int $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getMenustate(): ?int
    {
        return $this->menustate;
    }

    public function setMenustate(int $menustate): self
    {
        $this->menustate = $menustate;

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

    public function getOrdernr(): ?int
    {
        return $this->ordernr;
    }

    public function setOrdernr(int $ordernr): self
    {
        $this->ordernr = $ordernr;

        return $this;
    }

    public function getZonesoftcode(): ?string
    {
        return $this->zone_soft_code;
    }

    public function setZonesoftcode(?string $zone_soft_code): self
    {
        $this->zone_soft_code = $zone_soft_code;

        return $this;
    }

    public function getTimein(): ?string
    {
        return $this->timein;
    }

    public function setTimein(?string $timein): self
    {
        $this->timein = $timein;

        return $this;
    }

    public function getTimeout(): ?string
    {
        return $this->timeout;
    }

    public function setTimeout(?string $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function getTimeinm(): ?string
    {
        return $this->timeinm;
    }

    public function setTimeinm(?string $timeinm): self
    {
        $this->timeinm = $timeinm;

        return $this;
    }

    public function getTimeoutm(): ?string
    {
        return $this->timeoutm;
    }

    public function setTimeoutm(?string $timeoutm): self
    {
        $this->timeoutm = $timeoutm;

        return $this;
    }

    public function isHighlight(): ?bool
    {
        return $this->highlight;
    }

    public function setHighlight(?bool $highlight): self
    {
        $this->highlight = $highlight;

        return $this;
    }

    public function getBrandId(): ?int
    {
        return $this->brand_id;
    }

    public function setBrandId(?int $brand_id): self
    {
        $this->brand_id = $brand_id;

        return $this;
    }

    public function getMaterialId(): ?int
    {
        return $this->material_id;
    }

    public function setMaterialId(?int $material_id): self
    {
        $this->material_id = $material_id;

        return $this;
    }

    public function getBoreId(): ?int
    {
        return $this->bore_id;
    }

    public function setBoreId(?int $bore_id): self
    {
        $this->bore_id = $bore_id;

        return $this;
    }

    public function getTypeId(): ?int
    {
        return $this->type_id;
    }

    public function setTypeId(?int $type_id): self
    {
        $this->type_id = $type_id;

        return $this;
    }

    public function getModelId(): ?int
    {
        return $this->model_id;
    }

    public function setModelId(?int $model_id): self
    {
        $this->model_id = $model_id;

        return $this;
    }

    public function getTagId(): ?int
    {
        return $this->tag_id;
    }

    public function setTagId(?int $tag_id): self
    {
        $this->tag_id = $tag_id;

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

    public function isGrid(): ?bool
    {
        return $this->grid;
    }

    public function setGrid(?bool $grid): self
    {
        $this->grid = $grid;

        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(?bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getIva(): ?float
    {
        return $this->iva;
    }

    public function setIva(?float $iva): self
    {
        $this->iva = $iva;

        return $this;
    }



    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): self
    {
        $this->stock = $stock;
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

    public function getEquipamentId(): ?int
    {
        return $this->equipament_id;
    }

    public function setEquipamentId(int $equipament_id): self
    {
        $this->equipament_id = $equipament_id;

        return $this;
    }
}
