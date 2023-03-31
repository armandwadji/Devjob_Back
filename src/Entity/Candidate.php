<?php

namespace App\Entity;

use App\Repository\CandidateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CandidateRepository::class)]
class Candidate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Un candidat doit avoir un nom.')]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'Le nom d\'un candidat doit contenir au moins 2 caractères',
        maxMessage: 'Le nom d\'un candidat doit contenir maximum 50 caractères'
    )]
    private ?string $firstname = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Un candidat doit avoir un prénom.')]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'Le prénom d\'un candidat doit contenir au moins 2 caractères',
        maxMessage: 'Le prénom d\'un candidat doit contenir maximum 50 caractères'
    )]
    private ?string $lastname = null;

    #[ORM\Column(length: 255)]
    #[Assert\Email(message: 'Veuillez saisir un email valide.')]
    #[Assert\Length(
        max: 180,
        maxMessage: 'L\' email d\'un utilisateur doit contenir maximum 180 caractères'
    )]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::BIGINT)]
    #[Assert\NotBlank(message: 'Un numéro de téléphone est requis.')]
    #[Assert\Positive(message:'Le numéro de téléphone doit être une valeur positive.')]
    private ?string $telephone = null;

    #[ORM\ManyToMany(targetEntity: Offer::class, inversedBy: 'candidates')]
    private Collection $offer;

    public function __construct()
    {
        $this->offer = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * @return Collection<int, Offer>
     */
    public function getOffer(): Collection
    {
        return $this->offer;
    }

    public function addOffer(Offer $offer): self
    {
        if (!$this->offer->contains($offer)) {
            $this->offer->add($offer);
        }

        return $this;
    }

    public function removeOffer(Offer $offer): self
    {
        $this->offer->removeElement($offer);

        return $this;
    }
}
