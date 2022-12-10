<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

class PaginationService
{
    const DEFAULT_LIMIT = 100;

    public static function getLimit(Request $request)
    {
        return $request->get('limit') ?? self::DEFAULT_LIMIT;
    }

    public static function getOffset(Request $request)
    {
        return (int) $request->get('offset');
    }
}
