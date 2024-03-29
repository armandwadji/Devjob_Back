<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\RequirementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;


#[ORM\Entity(repositoryClass: RequirementRepository::class)]
class Requirement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\OneToOne(inversedBy: 'requirement', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Offer $offer = null;

    #[ORM\OneToMany(mappedBy: 'requirement', targetEntity: RequirementItem::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $requirementItems;

    public function __construct()
    {
        $this->requirementItems = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getOffer(): ?Offer
    {
        return $this->offer;
    }

    public function setOffer(Offer $offer): self
    {
        $this->offer = $offer;

        return $this;
    }

    /**
     * @return Collection<int, RequirementItem>
     */
    public function getRequirementItems(): Collection
    {
        return $this->requirementItems;
    }

    public function addRequirementItem(RequirementItem $requirementItem): self
    {
        if (!$this->requirementItems->contains($requirementItem)) {
            $this->requirementItems->add($requirementItem);
            $requirementItem->setRequirement($this);
        }

        return $this;
    }

    public function removeRequirementItem(RequirementItem $requirementItem): self
    {
        if ($this->requirementItems->removeElement($requirementItem)) {
            // set the owning side to null (unless already changed)
            if ($requirementItem->getRequirement() === $this) {
                $requirementItem->setRequirement(null);
            }
        }

        return $this;
    }

}
