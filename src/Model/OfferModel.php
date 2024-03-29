<?php

namespace App\Model;

use App\Entity\Offer;
use App\Entity\RoleItem;
use App\Entity\RequirementItem;
use Symfony\Component\Serializer\Annotation\Groups;

final class OfferModel
{
    private  const OFFER_DETAIL = 'offer:detail' ;
    private  const OFFER_LIST = 'offer:list' ;
    /**
     * Constructor of offer model
     * @param string $apply
     * @param string $company
     * @param string $contract
     * @param string $description
     * @param int $id
     * @param string $location
     * @param string $logo
     * @param string $logoBackground
     * @param string $position
     * @param int $postedAt
     * @param array $requirements
     * @param array $role
     * @param string $website
     */
    public function __construct(
        #[Groups(self::OFFER_DETAIL)]
        private readonly string $apply,

        #[Groups([self::OFFER_LIST, self::OFFER_DETAIL])]
        private readonly string $company,

        #[Groups([self::OFFER_LIST, self::OFFER_DETAIL])]
        private readonly string $contract,

        #[Groups(self::OFFER_DETAIL)]
        private readonly string $description,

        #[Groups([self::OFFER_LIST, self::OFFER_DETAIL])]
        private readonly int $id,

        #[Groups([self::OFFER_LIST, self::OFFER_DETAIL])]
        private readonly string $location,

        #[Groups([self::OFFER_LIST, self::OFFER_DETAIL])]
        private readonly string $logo,

        #[Groups([self::OFFER_LIST, self::OFFER_DETAIL])]
        private readonly string $logoBackground,

        #[Groups([self::OFFER_LIST, self::OFFER_DETAIL])]
        private readonly string $position,

        #[Groups([self::OFFER_LIST, self::OFFER_DETAIL])]
        private readonly int $postedAt,

        #[Groups(self::OFFER_DETAIL)]
        private readonly array $requirements,

        #[Groups(self::OFFER_DETAIL)]
        private readonly array $role,

        #[Groups(self::OFFER_DETAIL)]
        private readonly string $website,
    ) {
    }

    /**
     * This method construct offer model
     * @param Offer $offer
     * @return OfferModel
     */
    public static function fromOfferEntity(Offer $offer): OfferModel
    {
        return new self(
            apply           : $offer->getBaseUrl() . '/' . 'offers/' . $offer->getId() . '/apply',
            company         : $offer->getCompany()->getName(),
            contract        : $offer->getContract()->getName(),
            description     : $offer->getDescription(),
            id              : $offer->getId(),
            location        : $offer->getCompany()->getCountry(),
            logo            : $offer->getCompany()->getImageName() ? $offer->getBaseUrl() . '/images/company/' . $offer->getCompany()->getImageName() : 'https://picsum.photos/id/' . $offer->getCompany()->getId() . '/250/250',
            logoBackground  : $offer->getCompany()->getColor(),
            position        : $offer->getName(),
            postedAt        : self::timeStamp($offer->getCreatedAt()),
            requirements    : self::requirements($offer),
            role            : self::roles($offer),
            website         : $offer->getUrl(),
        );
    }

    /**
     * This method construct offers model
     * @param array $offers
     * @return array
     */
    public static function fromOfferEntities(array $offers): array
    {
        return array_map(self::fromOfferEntity(...), $offers);
    }

    /**
     * This method show requirement description and items
     * @param Offer $offer
     * @return array
     */
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

    /**
     * This method show roles description ans items
     * @param Offer $offer
     * @return array
     */
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

    /**
     * This method show timeStamp of offer
     * @param \DateTimeImmutable $createdAt
     * @return int
     */
    public static function timeStamp(\DateTimeImmutable $createdAt): int
    {
        return (int) $createdAt->format('Uv');
    }

    /**
     * Get the value of apply
     */
    public function getApply(): string
    {
        return $this->apply;
    }

    /**
     * Get the value of company
     */
    public function getCompany(): string
    {
        return $this->company;
    }

    /**
     * Get the value of contract
     */
    public function getContract(): string
    {
        return $this->contract;
    }

    /**
     * Get the value of description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get the value of id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the value of location
     */
    public function getLocation(): string
    {
        return $this->location;
    }

    /**
     * Get the value of logo
     */
    public function getLogo(): string
    {
        return $this->logo;
    }

    /**
     * Get the value of logoBackground
     */
    public function getLogoBackground(): string
    {
        return $this->logoBackground;
    }

    /**
     * Get the value of position
     */
    public function getPosition(): string
    {
        return $this->position;
    }

    /**
     * Get the value of postedAt
     */
    public function getPostedAt(): int
    {
        return $this->postedAt;
    }

    /**
     * Get the value of requirements
     */
    public function getRequirements(): array
    {
        return $this->requirements;
    }

    /**
     * Get the value of role
     */
    public function getRole(): array
    {
        return $this->role;
    }

    /**
     * Get the value of website
     */
    public function getWebsite(): string
    {
        return $this->website;
    }
}
