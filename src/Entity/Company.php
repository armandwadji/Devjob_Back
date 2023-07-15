<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Intl\Countries;


use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[UniqueEntity('name', message: "Ce nom d'entreprise est déja pris.")]
#[Vich\Uploadable]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Une entreprise doit avoir un nom.')]

    #[SerializedName('logo')]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Une entreprise doit avoir une couleur de fond.')]
    #[Assert\CssColor(
        formats: Assert\CssColor::RGB,
        message: 'La couleur doit être en format rgb.'
    )]

    #[SerializedName('logoBackground')]
    private ?string $color = null;

    // ************IMAGE ************
    #[Assert\File(
        extensions: ['jpg', 'jpeg', 'png', 'PNG'],
        extensionsMessage: 'Veuillez choisir une image valide.',
    )]
    #[Vich\UploadableField(mapping: 'company_images', fileNameProperty: 'imageName')]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $imageName = null;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Offer::class, orphanRemoval: true)]
    private Collection $offer;

    #[ORM\OneToOne(inversedBy: 'company', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255)]

    #[SerializedName('position')]
    #[Groups(['offer:read'])]
    private ?string $country = null;

    public function __construct()
    {
        $this->offer = new ArrayCollection();
        $this->updatedAt = new \DateTimeImmutable();
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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

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
            $offer->setCompany($this);
        }

        return $this;
    }

    public function removeOffer(Offer $offer): self
    {
        if ($this->offer->removeElement($offer)) {
            if ($offer->getCompany() === $this) {
                $offer->setCompany(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|null $imageFile
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    /**
     * This method Encode country name in isoCode 3
     * @return void
     */
    public function countryEncode(): void
    {
        $isoCode2 = array_search($this->country, Countries::getNames(), true);
        $isoCode3 = Countries::getAlpha3Code($isoCode2);
        $this->setCountry($isoCode3);
    }

    /**
     * This method Decode country name in string
     * @return void
     */
    public function countryDecode(): void
    {
        $this->setCountry(Countries::getAlpha3Name($this->country));
    }

    /**
     * Get the value of emojiCountry
     * @return ?string
     */
    public function getEmojiCountry(): ?string
    {
        $isoCode2 = array_search($this->getCountry(), Countries::getNames(), true);

        return implode(
            '',
            array_map(
                fn ($letter) => mb_chr(ord($letter) % 32 + 0x1F1E5),
                str_split($isoCode2)
            )
        );
    }

    public function __toString()
    {
        return $this->name ?: '';
    }


}
