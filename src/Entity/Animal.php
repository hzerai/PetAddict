<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Cache(usage="NONSTRICT_READ_WRITE", region="animals_region")
 * @ORM\Entity(repositoryClass=AnimalRepository::class) @ORM\HasLifecycleCallbacks
 */
class Animal
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255 ,  nullable=true)
     */
    private $espece;

    /**
     * @ORM\Column(type="string", length=255 ,  nullable=true)
     */
    private $sexe;

    /**
     * @ORM\Column(type="string", length=255 ,  nullable=true)
     */
    private $taille;

    /**
     * @ORM\Column(type="integer",  nullable=true)
     */
    private $age;

    /**
     * @ORM\Column(type="string", length=255 ,  nullable=true)
     */
    private $couleur;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @ORM\OneToOne(targetEntity=Adoption::class, mappedBy="animal", cascade={"persist", "remove"})
     */
    private $adoption;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nom;

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

    /**
     * @ORM\OneToOne(targetEntity=Found::class, mappedBy="animal", cascade={"persist", "remove"})
     */
    private $found;
    /**
     * @ORM\OneToOne(targetEntity=Lost::class, mappedBy="animal", cascade={"persist", "remove"})
     */
    private $lost;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEspece(): ?string
    {
        return $this->espece;
    }

    public function setEspece(string $espece): self
    {
        $this->espece = $espece;

        return $this;
    }

    public function getSexe(): ?string
    {
        return $this->sexe;
    }

    public function setSexe(string $sexe): self
    {
        $this->sexe = $sexe;

        return $this;
    }

    public function getTaille(): ?string
    {
        return $this->taille;
    }

    public function setTaille(string $taille): self
    {
        $this->taille = $taille;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(?int $age): self
    {
        $this->age = $age;

        return $this;
    }

    public function getCouleur(): ?string
    {
        return $this->couleur;
    }

    public function setCouleur(string $couleur): self
    {
        $this->couleur = $couleur;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

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
        if ($this->getAdoption()!== null){
            $this->createdBy = $this->getAdoption()->getUser()->getUserName();
        }
        if ($this->getFound()!== null){

        $this->createdBy = $this->getFound()->getUser()->getUserName();
        }
    }

    /** @ORM\PreUpdate */
    public function preUpdate()
    {
        $this->updatedAt = new DateTime();
        $this->updatedBy = $this->getAdoption()->getUser()->getUserName();
    }

    public function getAdoption(): ?Adoption
    {
        return $this->adoption;
    }

    public function setAdoption(Adoption $adoption): self
    {
        // set the owning side of the relation if necessary
        if ($adoption->getAnimal() !== $this) {
            $adoption->setAnimal($this);
        }

        $this->adoption = $adoption;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getFound(): ?Found
    {
        return $this->found;
    }


    public function setFound(?Found $found): self
    {
        // unset the owning side of the relation if necessary
        if ($found === null && $this->found !== null) {
            $this->found->setAnimal(null);
        }

        // set the owning side of the relation if necessary
        if ($found !== null && $found->getAnimal() !== $this) {
            $found->setAnimal($this);
        }

        $this->found = $found;

        return $this;
    }
    public function getLost(): ?Lost
    {
        return $this->lost;
    }


    public function setLost(?Lost $lost): self
    {
        // unset the owning side of the relation if necessary
        if ($lost === null && $this->lost !== null) {
            $this->lost->setAnimal(null);
        }

        // set the owning side of the relation if necessary
        if ($lost !== null && $lost->getAnimal() !== $this) {
            $lost->setAnimal($this);
        }

        $this->lost = $lost;

        return $this;
    }
}
