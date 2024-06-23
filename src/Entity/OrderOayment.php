<?php

namespace App\Entity;

use App\Repository\OrderOaymentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderOaymentRepository::class)
 */
class OrderOayment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $multibanco;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isMultibanco(): ?bool
    {
        return $this->multibanco;
    }

    public function setMultibanco(?bool $multibanco): self
    {
        $this->multibanco = $multibanco;

        return $this;
    }
}
