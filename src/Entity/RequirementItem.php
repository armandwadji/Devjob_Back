<?php

namespace App\Entity;

use App\Repository\RequirementItemRepository;
use Symfony\Component\Serializer\Annotation\Groups;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RequirementItemRepository::class)]
class RequirementItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups('offer:detail')]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: Requirement::class, inversedBy: 'requirementItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Requirement $requirement = null;


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

    public function getRequirement(): ?Requirement
    {
        return $this->requirement;
    }

    public function setRequirement(?Requirement $requirement): self
    {
        $this->requirement = $requirement;

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }
}
