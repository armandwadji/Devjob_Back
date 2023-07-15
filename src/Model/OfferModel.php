<?php

namespace App\Model;

use App\Entity\Offer;
use App\Entity\RoleItem;
use App\Entity\RequirementItem;
use Symfony\Component\Serializer\Annotation\Groups;

final class OfferModel
{
    public function __construct(
        #[Groups('offer:detail')]
        private readonly string $apply,

        #[Groups(['offer:list', 'offer:detail'])]
        private readonly string $company,

        #[Groups(['offer:list', 'offer:detail'])]
        private readonly string $contract,

        #[Groups('offer:detail')]
        private readonly string $description,

        #[Groups(['offer:list', 'offer:detail'])]
        private readonly int $id,

        #[Groups(['offer:list', 'offer:detail'])]
        private readonly string $location,

        #[Groups(['offer:list', 'offer:detail'])]
        private readonly string $logo,

        #[Groups(['offer:list', 'offer:detail'])]
        private readonly string $logoBackground,

        #[Groups(['offer:list', 'offer:detail'])]
        private readonly string $position,

        #[Groups(['offer:list', 'offer:detail'])]
        private readonly int $postedAt,

        #[Groups('offer:detail')]
        private readonly array $requirements,

        #[Groups('offer:detail')]
        private readonly array $role,

        #[Groups('offer:detail')]
        private readonly string $website,
    ) {
    }

    public static function fromOfferEntity(Offer $offer)
    {
        return new self(
            apply: $offer->getBaseUrl() . '/' . 'offers/' . $offer->getId() . '/apply',
            company: $offer->getCompany()->getName(),
            contract: $offer->getContract()->getName(),
            description: $offer->getDescription(),
            id: $offer->getId(),
            location: $offer->getCompany()->getCountry(),
            logo: $offer->getCompany()->getImageName() ? $offer->getBaseUrl() . '/images/company/' . $offer->getCompany()->getImageName() : 'https://picsum.photos/id/' . $offer->getCompany()->getId() . '/250/250',
            logoBackground: $offer->getCompany()->getColor(),
            position: $offer->getName(),
            postedAt: self::timeStamp($offer->getCreatedAt()),
            requirements: self::requirements($offer),
            role: self::roles($offer),
            website: $offer->getUrl(),
        );
    }

    public static function fromOfferEntities(array $offers): array
    {
        return array_map(self::fromOfferEntity(...), $offers);
    }

    public static function requirements(Offer $offer): array
    {
        return [
            'content' => $offer->getRequirement()->getContent(),
            'items' => $offer->getRequirement()->getRequirementItems()->map(
                static function (RequirementItem $requirementItem): string {
                    return $requirementItem->getName();
                }
            )->toArray()
        ];
    }

    public static function roles(Offer $offer): array
    {
        return [
            'content' => $offer->getRole()->getContent(),
            'items' => $offer->getRole()->getRoleItems()->map(
                static function (RoleItem $roleItem): string {
                    return $roleItem->getName();
                }
            )->toArray()
        ];
    }

    public static function timeStamp(\DateTimeImmutable $createdAt): int
    {
        return (int) $createdAt->format('Uv');
    }

    /**
     * Get the value of apply
     */
    public function getApply()
    {
        return $this->apply;
    }

    /**
     * Get the value of company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Get the value of contract
     */
    public function getContract()
    {
        return $this->contract;
    }

    /**
     * Get the value of description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Get the value of logo
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Get the value of logoBackground
     */
    public function getLogoBackground()
    {
        return $this->logoBackground;
    }

    /**
     * Get the value of position
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Get the value of postedAt
     */
    public function getPostedAt()
    {
        return $this->postedAt;
    }

    /**
     * Get the value of requirements
     */
    public function getRequirements()
    {
        return $this->requirements;
    }

    /**
     * Get the value of role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Get the value of website
     */
    public function getWebsite()
    {
        return $this->website;
    }
}
