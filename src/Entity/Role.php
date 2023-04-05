<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;
#[ORM\Entity(repositoryClass: RoleRepository::class)]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups('offer:read')]
    private ?string $content = null;

    #[ORM\OneToOne(inversedBy: 'role', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Offer $offer = null;

    #[ORM\OneToMany(mappedBy: 'role', targetEntity: RoleItem::class, orphanRemoval: true, cascade: ['persist'] )]
    #[Groups('offer:read')]
    private Collection $roleItems;

    public function __construct()
    {
        $this->roleItems = new ArrayCollection();
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
     * @return Collection<int, RoleItem>
     */
    public function getRoleItems(): Collection
    {
        return $this->roleItems;
    }

    public function addRoleItem(RoleItem $roleItem): self
    {
        if (!$this->roleItems->contains($roleItem)) {
            $this->roleItems->add($roleItem);
            $roleItem->setRole($this);
        }

        return $this;
    }

    public function removeRoleItem(RoleItem $roleItem): self
    {
        if ($this->roleItems->removeElement($roleItem)) {
            // set the owning side to null (unless already changed)
            if ($roleItem->getRole() === $this) {
                $roleItem->setRole(null);
            }
        }

        return $this;
    }
}
