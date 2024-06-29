<?php

namespace App\Entity;

use App\Repository\StripeConfigRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=StripeConfigRepository::class)
 */
class StripeConfig
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
    private $dev_token;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    private $dev_public_token;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    private $public_token;

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

    public function getDevToken(): ?string
    {
        return $this->dev_token;
    }

    public function setDevToken(?string $dev_token): self
    {
        $this->dev_token = $dev_token;

        return $this;
    }

    public function getDevPublicToken(): ?string
    {
        return $this->dev_public_token;
    }

    public function setDevPublicToken(?string $dev_public_token): self
    {
        $this->dev_public_token = $dev_public_token;

        return $this;
    }

    public function getPublicToken(): ?string
    {
        return $this->public_token;
    }

    public function setPublicToken(?string $public_token): self
    {
        $this->public_token = $public_token;

        return $this;
    }
}
