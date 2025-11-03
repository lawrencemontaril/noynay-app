<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Database\Eloquent\Collection;

class PaginatedJsonResource extends JsonResource
{
    /**
     * Create a new resource collection instance.
     *
     * @param mixed $resource
     * @return PaginatedJsonResourceCollection
     */
    protected static function newCollection($resource): PaginatedJsonResourceCollection
    {
        return new PaginatedJsonResourceCollection($resource, static::class);
    }
}
