<?php

namespace App\Entity;

use App\Repository\SystemConfigRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SystemConfigRepository::class)
 * @ORM\Table(name="systemconfig")
 */
class SystemConfig
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
    private $nif;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone2;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $emailusername;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $emailhost;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $emailContact;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $emailpassword;


    /**
     * @ORM\Column(type="string", length=255)
     */
    private $companyname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $hoursopen;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $facebook;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $instagram;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tripadvisorlink;


    /**
     * @ORM\Column(type="string", length=1024, nullable=true)
     */
    private $location;

    /**
     * @ORM\Column(type="boolean")
     */
    private $production;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $fixed_fee;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $youtube_link;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNif(): ?string
    {
        return $this->nif;
    }

    public function setNif(?string $nif): self
    {
        $this->nif = $nif;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPhone1(): ?string
    {
        return $this->phone1;
    }

    public function setPhone1(string $phone1): self
    {
        $this->phone1 = $phone1;

        return $this;
    }

    public function getPhone2(): ?string
    {
        return $this->phone2;
    }

    public function setPhone2(string $phone2): self
    {
        $this->phone2 = $phone2;

        return $this;
    }

    public function getEmailusername(): ?string
    {
        return $this->emailusername;
    }

    public function setEmailusername(string $emailusername): self
    {
        $this->emailusername = $emailusername;

        return $this;
    }

    public function getEmailhost(): ?string
    {
        return $this->emailhost;
    }

    public function setEmailhost(string $emailhost): self
    {
        $this->emailhost = $emailhost;

        return $this;
    }

    public function getEmailContact(): ?string
    {
        return $this->emailContact;
    }

    public function setEmailContact(string $emailContact): self
    {
        $this->emailContact = $emailContact;

        return $this;
    }

    public function getEmailpassword(): ?string
    {
        return $this->emailpassword;
    }

    public function setEmailpassword(string $emailpassword): self
    {
        $this->emailpassword = $emailpassword;

        return $this;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyname;
    }

    public function setCompanyName(string $companyName): self
    {
        $this->companyname = $companyName;

        return $this;
    }

    public function getHoursOpen(): ?string
    {
        return $this->hoursopen;
    }

    public function setHoursOpen(string $hoursOpen): self
    {
        $this->hoursopen = $hoursOpen;

        return $this;
    }

    public function getFacebook(): ?string
    {
        return $this->facebook;
    }

    public function setFacebook(string $facebook): self
    {
        $this->facebook = $facebook;

        return $this;
    }

    public function getInstagram(): ?string
    {
        return $this->instagram;
    }

    public function setInstagram(string $instagram): self
    {
        $this->instagram = $instagram;

        return $this;
    }

    public function getTripadvisorLink(): ?string
    {
        return $this->tripadvisorlink;
    }

    public function setTripadvisorLink(string $tripadvisorlink): self
    {
        $this->tripadvisorlink = $tripadvisorlink;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function isProduction(): ?bool
    {
        return $this->production;
    }

    public function setProduction(bool $production): self
    {
        $this->production = $production;

        return $this;
    }

    public function getFixedFee(): ?float
    {
        return $this->fixed_fee;
    }

    public function setFixedFee(?float $fixed_fee): self
    {
        $this->fixed_fee = $fixed_fee;

        return $this;
    }

    public function getYoutubeLink(): ?string
    {
        return $this->youtube_link;
    }

    public function setYoutubeLink(?string $youtube_link): self
    {
        $this->youtube_link = $youtube_link;

        return $this;
    }
}
