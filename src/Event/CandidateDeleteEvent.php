<?php

namespace App\Event;

use App\Entity\Candidate;
use Symfony\Contracts\EventDispatcher\Event;

class CandidateDeleteEvent extends Event
{
    public function __construct(
        public readonly Candidate $candidate
        ){
    }
}