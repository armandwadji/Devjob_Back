<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use App\EntityListener\UserListener;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity('email', message: "Cet email existe déja en base de donnée.")]
#[ORM\EntityListeners([UserListener::class])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Un contact doit avoir un nom.')]
    #[Assert\NotNull(message: 'Un contact doit avoir un nom.')]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'Le nom d\'un contact doit contenir au moins 2 caractères',
        maxMessage: 'Le nom d\'un contact doit contenir maximum 50 caractères'
    )]
    private ?string $firstname = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Un contact doit avoir un prénom.')]
    #[Assert\NotNull(message: 'Un contact doit avoir un prénom.')]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'Le prénom d\'un contact doit contenir au moins 2 caractères',
        maxMessage: 'Le prénom d\'un contact doit contenir maximum 50 caractères'
    )]
    private ?string $lastname = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\Email(message: 'Veuillez saisir un email valide.')]
    #[Assert\NotBlank(message: 'Un contact doit avoir un email.')]
    #[Assert\NotNull(message: 'Un contact doit avoir un email.')]
    #[Assert\Length(
        max: 180,
        maxMessage: 'L\' email d\'un contact doit contenir maximum 180 caractères'
    )]
    private ?string $email = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Un utilisateur doit avoir minimum un rôle.')]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[Assert\Regex(
        pattern: '/^.{8,}$/',
        match: true,
        message: 'Le mot de passe doit contenir minimum 8 un caratères.',
    )]

    #[Assert\Regex(
        pattern: '/^(?=.*?[A-Z])/',
        match: true,
        message: 'Le mot de passe doit contenir au moins une lettre majuscule.',
    )]
    #[Assert\Regex(
        pattern: '/^(?=.*?[a-z])/',
        match: true,
        message: 'Le mot de passe doit contenir au moins une lettre minuscule.',
    )]
    #[Assert\Regex(
        pattern: '/^(?=.*?[0-9]).{2,}$/',
        match: true,
        message: 'Le mot de passe doit contenir au moins un chiffre.',
    )]
    #[Assert\Regex(
        pattern: '/^(?=.*?[#?!@$%^&*-]).{2,}$/',
        match: true,
        message: 'Le mot de passe doit contenir au moins un caratère spécial.',
    )]
    private ?string $plainPassword = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tokenRegistration = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $tokenRegistrationLifeTime = null;

    #[ORM\Column]
    private ?bool $isVerified = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Company $company = null;

    #[ORM\Column]
    private ?bool $isDeleted = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;


    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->roles[] = 'ROLE_USER';
        $this->isVerified = false;
        $this->isDeleted = false;
        $this->tokenRegistrationLifeTime = (new \DateTimeImmutable('now'))->add(new \DateInterval('P1D'));
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get the value of plainPassword
     *
     * @return ?string
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * Set the value of plainPassword
     *
     * @param ?string $plainPassword
     *
     * @return self
     */
    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getTokenRegistration(): ?string
    {
        return $this->tokenRegistration;
    }

    public function setTokenRegistration(?string $tokenRegistration): self
    {
        $this->tokenRegistration = $tokenRegistration;

        return $this;
    }

    public function getTokenRegistrationLifeTime(): ?\DateTimeInterface
    {
        return $this->tokenRegistrationLifeTime;
    }

    public function setTokenRegistrationLifeTime(\DateTimeInterface $tokenRegistrationLifeTime): self
    {
        $this->tokenRegistrationLifeTime = $tokenRegistrationLifeTime;

        return $this;
    }

    public function isIsVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

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

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(Company $company): self
    {
        // set the owning side of the relation if necessary
        if ($company->getUser() !== $this) {
            $company->setUser($this);
        }

        $this->company = $company;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

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

    public function isExpired()
    {
        return new \DateTimeImmutable('now') > $this->tokenRegistrationLifeTime && !$this->isVerified;
    }

    /**
     * This method Encode country name in isoCode 3
     * @return void
     */
    public function countryEncode()
    {
        $isoCode2 = array_search($this->getCompany()->getCountry(), Countries::getNames(), true);
        $isoCode3 = Countries::getAlpha3Code($isoCode2);
        $this->getCompany()->setCountry($isoCode3);
    }

    /**
     * This method Decode country name in string
     * @return void
     */
    public function countryDecode()
    {
        $this->getCompany()->setCountry(Countries::getAlpha3Name($this->getCompany()->getCountry()));
    }
}
