<?php

namespace App\DTO;

class CompanyStats
{
    public function __construct(
        public readonly string $companyName,
        public readonly int $reviewCount,
        public readonly float $avgRating,
    ) {
    }
}
