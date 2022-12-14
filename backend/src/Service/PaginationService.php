<?php

namespace App\Service;

use Doctrine\ORM\QueryBuilder;
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

    public static function paginate(QueryBuilder $queryBuilder, Request $request)
    {
        $limit = $request->get('limit');
        $offset = $request->get('offset') ?? 100;

        if ($limit) {
            $queryBuilder
                ->setMaxResults($limit)
                ->setFirstResult($offset);
        }
    }
}
