<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Landlord;

use App\Domain\Shared\Data\Api\V1\LandlordHealthData;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $payload = LandlordHealthData::fromRequestUser($request->user());

        return response()->json([
            'data' => $payload->toArray(),
        ]);
    }
}
