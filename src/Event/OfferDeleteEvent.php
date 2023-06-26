<?php

namespace App\Event;

use App\Entity\Offer;
use Symfony\Contracts\EventDispatcher\Event;

class OfferDeleteEvent extends Event
{
    public function __construct(
        public readonly Offer $offer
        ){
    }
}
