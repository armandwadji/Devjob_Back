<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\RoleItemRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RoleItemRepository::class)]
class RoleItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'La description d\'un role doit contenir au moins 2 caractères',
        maxMessage: 'La description d\'un role doit contenir maximum 255 caractères'
    )]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: Role::class, inversedBy: 'roleItems')]
    private ?Role $role = null;

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

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function __toString()
    {
        return $this->name?:'';
    }
}
