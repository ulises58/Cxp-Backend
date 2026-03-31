<?php

declare(strict_types=1);

namespace App\Domain\Shared\Data\Api\V1;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Misma forma que {@see ResourceCollection} paginada (data + links + meta).
 */
final class ApiV1PaginatedResponse
{
    /**
     * @param  callable(object): array<string, mixed>  $mapItem
     * @return array<string, mixed>
     */
    public static function fromPaginator(LengthAwarePaginator $page, callable $mapItem): array
    {
        $items = [];
        foreach ($page->items() as $model) {
            $items[] = $mapItem($model);
        }

        return [
            'data' => $items,
            'links' => [
                'first' => $page->url(1),
                'last' => $page->url($page->lastPage()),
                'prev' => $page->previousPageUrl(),
                'next' => $page->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $page->currentPage(),
                'from' => $page->firstItem(),
                'last_page' => $page->lastPage(),
                'path' => $page->path(),
                'per_page' => $page->perPage(),
                'to' => $page->lastItem(),
                'total' => $page->total(),
            ],
        ];
    }
}
