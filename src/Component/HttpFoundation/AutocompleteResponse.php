<?php

namespace App\Component\HttpFoundation;

use App\Service\EntityIdentifierService;
use App\Service\Pagination\Paginator;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class AutocompleteResponse.
 */
class AutocompleteResponse extends JsonResponse
{
    public function __construct(Paginator $paginator, EntityIdentifierService $identifierService, callable $textFct = null)
    {
        $items = [];

        foreach ($paginator as $entity) {
            $items[] = [
                'id'   => $identifierService->getEntityIdentifier($entity),
                'text' => $textFct ? call_user_func($textFct, $entity) : (string) $entity,
            ];
        }

        parent::__construct(
            [
                'total_count' => $paginator->count(),
                'results'     => $items,
            ]
        );
    }
}
