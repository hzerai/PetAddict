<?php

namespace App\Entity;

use DateTime;
use App\Repository\AdoptionRequestRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="animals_region")
 * @ORM\Entity(repositoryClass=AdoptionRequestRepository::class) @ORM\HasLifecycleCallbacks
 */
class AdoptionRequest
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
    private $status = "CREATED";


    private $user;


    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $hasTestamony  = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $createdBy;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $updatedBy;


    private $adoption;

    /**
     * @ORM\Column(type="integer")
     */
    private $userId;

    /**
     * @ORM\Column(type="integer")
     */
    private $adoptionId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getHasTestamony(): ?bool
    {
        return $this->hasTestamony;
    }

    public function setHasTestamony(bool $hasTestamony): self
    {
        $this->hasTestamony = $hasTestamony;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getAdoptionId(): ?int
    {
        return $this->adoptionId;
    }

    public function setAdoptionId(?int $adoptionId): self
    {
        $this->adoptionId = $adoptionId;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?string $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?string $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /** @ORM\PrePersist */
    public function prePersist()
    {
        $this->createdAt = new DateTime();
    }

    /** @ORM\PreUpdate */
    public function preUpdate()
    {
        $this->updatedAt = new DateTime();
    }

    public function getAdoption(): ?Adoption
    {
        return $this->adoption;
    }

    public function setAdoption(?Adoption $adoption): self
    {
        $this->adoption = $adoption;

        return $this;
    }
}
