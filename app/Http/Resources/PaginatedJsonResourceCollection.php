<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaginatedJsonResourceCollection extends AnonymousResourceCollection
{
    /**
     * Adjust pagination data format
     *
     * @param Request $request
     * @param array $paginated
     * @param array $default
     * @return array
     */
    public function paginationInformation(Request $request, array $paginated, array $default)
    {
        unset($default['links']);

        $default['meta'] = [
            'path' => $default['meta']['path'],
            'current_page' => $default['meta']['current_page'],
            'per_page' => $default['meta']['per_page'],
            'last_page' => $default['meta']['last_page'],
            'total' => $default['meta']['total'],
            'from_index' => $default['meta']['from'],
            'to_index' => $default['meta']['to'],
        ];

        return $default;
    }
}
