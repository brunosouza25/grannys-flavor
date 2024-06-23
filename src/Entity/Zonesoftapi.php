<?php

namespace App\Entity;

use App\Repository\ZonesoftapiRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ZonesoftapiRepository::class)
 */
class Zonesoftapi
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
    private $appsecret;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $appkey;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $storeid;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $token;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAppsecret(): ?string
    {
        return $this->appsecret;
    }

    public function setAppsecret(string $appsecret): self
    {
        $this->appsecret = $appsecret;

        return $this;
    }

    public function getAppkey(): ?string
    {
        return $this->appkey;
    }

    public function setAppkey(string $appkey): self
    {
        $this->appkey = $appkey;

        return $this;
    }

    public function getStoreid(): ?string
    {
        return $this->storeid;
    }

    public function setStoreid(string $storeid): self
    {
        $this->storeid = $storeid;

        return $this;
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
}
