<?php

namespace App\Services;

use App\Models\Track;
use Illuminate\Http\Request;

class RegionPricingService
{
    public function __construct(
        private readonly GeoLocationService $geoLocationService,
    ) {}

    public function resolveRegion(Request $request): ?string
    {
        return $this->geoLocationService->regionForIp($request->ip());
    }

    public function priceFor(Track $track, ?string $region): float
    {
        return $track->priceForRegion($region);
    }
}
