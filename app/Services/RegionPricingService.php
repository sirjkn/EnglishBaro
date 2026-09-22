<?php

namespace App\Services;

use App\Models\Course;
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

    public function priceFor(Course $course, ?string $region): float
    {
        return $course->priceForRegion($region);
    }
}
