<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Landlord;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [
                'scope' => 'landlord',
                'user' => [
                    'id' => $request->user()?->id,
                    'email' => $request->user()?->email,
                ],
            ],
        ]);
    }
}
