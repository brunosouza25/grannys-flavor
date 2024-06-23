<?php

namespace App\Entity;

use App\Repository\ZonemapdrawingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ZonemapdrawingRepository::class)
 */
class Zonemapdrawing
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
    private $idzone;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $cordinate;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lat;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lng;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdzone(): ?int
    {
        return $this->idzone;
    }

    public function setIdzone(int $idzone): self
    {
        $this->idzone = $idzone;

        return $this;
    }

    public function getCordinate(): ?string
    {
        return $this->cordinate;
    }

    public function setCordinate(string $cordinate): self
    {
        $this->cordinate = $cordinate;

        return $this;
    }

    public function getLat(): ?string
    {
        return $this->lat;
    }

    public function setLat(string $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLng(): ?string
    {
        return $this->lng;
    }

    public function setLng(string $lng): self
    {
        $this->lng = $lng;

        return $this;
    }
}
