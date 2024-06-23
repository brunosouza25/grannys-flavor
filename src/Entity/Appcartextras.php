<?php

namespace App\Entity;

use App\Repository\AppcartextrasRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AppcartextrasRepository::class)
 */
class Appcartextras
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
}
