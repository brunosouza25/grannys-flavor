<?php

namespace App\Entity;

use App\Repository\ApporderextraRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ApporderextraRepository::class)
 */
class Apporderextra
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
    private $orderid;

    /**
     * @ORM\Column(type="integer")
     */
    private $extraid;

    /**
     * @ORM\Column(type="integer")
     */
    private $itemid;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderid(): ?int
    {
        return $this->orderid;
    }

    public function setOrderid(int $orderid): self
    {
        $this->orderid = $orderid;

        return $this;
    }

    public function getExtraid(): ?int
    {
        return $this->extraid;
    }

    public function setExtraid(int $extraid): self
    {
        $this->extraid = $extraid;

        return $this;
    }

    public function getItemid(): ?int
    {
        return $this->itemid;
    }

    public function setItemid(int $itemid): self
    {
        $this->itemid = $itemid;

        return $this;
    }
}
