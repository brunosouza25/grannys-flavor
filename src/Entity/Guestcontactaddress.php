<?php

namespace App\Entity;

use App\Repository\GuestcontactaddressRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GuestcontactaddressRepository::class)
 */
class Guestcontactaddress
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
    private $session;

    /**
     * @ORM\Column(type="integer")
     */
    private $idcontact;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $street;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $postalcode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $referencecode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lantitude;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $longitude;

    /**
     * @ORM\Column(type="integer", nullable=true, nullable=true)
     */
    private $deliveryzoneid;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $status;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSession(): ?string
    {
        return $this->session;
    }

    public function setSession(string $session): self
    {
        $this->session = $session;

        return $this;
    }

    public function getIdcontact(): ?int
    {
        return $this->idcontact;
    }

    public function setIdcontact(int $idcontact): self
    {
        $this->idcontact = $idcontact;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): self
    {
        $this->street = $street;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getPostalcode(): ?string
    {
        return $this->postalcode;
    }

    public function setPostalcode(string $postalcode): self
    {
        $this->postalcode = $postalcode;

        return $this;
    }

    public function getReferencecode(): ?string
    {
        return $this->referencecode;
    }

    public function setReferencecode(?string $referencecode): self
    {
        $this->referencecode = $referencecode;

        return $this;
    }

    public function getLantitude(): ?string
    {
        return $this->lantitude;
    }

    public function setLantitude(string $lantitude): self
    {
        $this->lantitude = $lantitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getDeliveryzoneid(): ?int
    {
        return $this->deliveryzoneid;
    }

    public function setDeliveryzoneid(?int $deliveryzoneid): self
    {
        $this->deliveryzoneid = $deliveryzoneid;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }
}
