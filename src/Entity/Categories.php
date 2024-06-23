<?php

namespace App\Entity;

use App\Repository\CategoriesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CategoriesRepository::class)
 */
class Categories
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
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ordernr;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="integer")
     */
    private $state;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $menustate;

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
     * @ORM\Column(type="integer", nullable=true)
     */
    private $level;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $parent_id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $zone_soft_id;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getOrdernr(): ?int
    {
        return $this->ordernr;
    }

    public function setOrdernr(?int $ordernr): self
    {
        $this->ordernr = $ordernr;

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

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getParentId(): ?int
    {
        return $this->parent_id;
    }

    public function setParentId(?int $parent_id): self
    {
        $this->parent_id = $parent_id;

        return $this;
    }

    public function getZoneSoftId(): ?int
    {
        return $this->zone_soft_id;
    }

    public function setZoneSoftId(?int $zone_soft_id): self
    {
        $this->zone_soft_id = $zone_soft_id;

        return $this;
    }
}
