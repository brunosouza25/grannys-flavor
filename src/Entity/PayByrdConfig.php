<?php

namespace App\Entity;

use App\Repository\PayByrdConfigRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PayByrdConfigRepository::class)
 */
class PayByrdConfig
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $token;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $token_dev;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getTokenDev(): ?string
    {
        return $this->token_dev;
    }

    public function setTokenDev(?string $token_dev): self
    {
        $this->token_dev = $token_dev;

        return $this;
    }
}
