<?php

namespace App\Entity;

use App\Repository\EmailsToSendRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EmailsToSendRepository::class)
 */
class EmailsToSend
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $body;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $destination_email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $destination_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $subject;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $status;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getDestinationEmail(): ?string
    {
        return $this->destination_email;
    }

    public function setDestinationEmail(?string $destination_email): self
    {
        $this->destination_email = $destination_email;

        return $this;
    }

    public function getDestinationName(): ?string
    {
        return $this->destination_name;
    }

    public function setDestinationName(?string $destination_name): self
    {
        $this->destination_name = $destination_name;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;

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
