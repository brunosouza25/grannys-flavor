<?php

namespace App\Entity;

use App\Repository\VivawalletRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=VivawalletRepository::class)
 */
class Vivawallet
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
    private $custumerid;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $apikey;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $acesstoken;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $typeuse;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $clientid;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $secret;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustumerid(): ?string
    {
        return $this->custumerid;
    }

    public function setCustumerid(?string $custumerid): self
    {
        $this->custumerid = $custumerid;

        return $this;
    }

    public function getApikey(): ?string
    {
        return $this->apikey;
    }

    public function setApikey(?string $apikey): self
    {
        $this->apikey = $apikey;

        return $this;
    }

    public function getAcesstoken(): ?string
    {
        return $this->acesstoken;
    }

    public function setAcesstoken(?string $acesstoken): self
    {
        $this->acesstoken = $acesstoken;

        return $this;
    }

    public function getTypeuse(): ?string
    {
        return $this->typeuse;
    }

    public function setTypeuse(?string $typeuse): self
    {
        $this->typeuse = $typeuse;

        return $this;
    }

    public function getClientId(): ?string
    {
        return $this->clientid;
    }

    public function setClientId(?string $clientid): self
    {
        $this->clientid = $clientid;

        return $this;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(?string $secret): self
    {
        $this->secret = $secret;

        return $this;
    }
}
