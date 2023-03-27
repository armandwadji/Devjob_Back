<?php

namespace App\Entity;

use App\Entity\Contract;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use App\Repository\OfferRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: OfferRepository::class)]
#[UniqueEntity('name', message: "Ce nom d'offre existe déja en base de donnée.")]
class Offer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Une offre doit avoir un nom.')]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'Le nom d\'une offre doit contenir au moins 2 caractères',
        maxMessage: 'Le nom d\'une offre doit contenir maximum 50 caractères'
    )]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Une offre doit avoir une description.')]
    #[Assert\Length(
        min: 10,
        minMessage: 'La description d\'une offre doit contenir au moins 10 caractères',
    )]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Une offre doit avoir un lien pour postuler.')]
    #[Assert\Url(
        message: 'Le lien {{ value }} n\'est pas un url valide.',
    )]
    
    private ?string $url = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'offers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Contract $contract = null;

    #[ORM\OneToOne(mappedBy: 'offer', cascade: ['persist', 'remove'])]
    private ?Requirement $requirement = null;

    #[ORM\OneToOne(mappedBy: 'offer', cascade: ['persist', 'remove'])]
    private ?Role $role = null;

    #[ORM\ManyToOne(inversedBy: 'offer')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    /**
     * Construteur pour l'initialisation de la date de création
     */
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getContract(): ?Contract
    {
        return $this->contract;
    }

    public function setContract(?Contract $contract): self
    {
        $this->contract = $contract;

        return $this;
    }

    public function getRequirement(): ?Requirement
    {
        return $this->requirement;
    }

    public function setRequirement(Requirement $requirement): self
    {
        // set the owning side of the relation if necessary
        if ($requirement->getOffer() !== $this) {
            $requirement->setOffer($this);
        }

        $this->requirement = $requirement;

        return $this;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(Role $role): self
    {
        // set the owning side of the relation if necessary
        if ($role->getOffer() !== $this) {
            $role->setOffer($this);
        }

        $this->role = $role;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }
}
