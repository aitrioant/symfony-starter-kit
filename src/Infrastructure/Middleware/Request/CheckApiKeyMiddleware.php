<?php

namespace App\Infrastructure\Middleware\Request;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApiKeyMiddleware implements RequestMiddleware
{
    public function process(Request $request): ?Response
    {
        if ($request->headers->get('X-API-Key') !== 'secret') {
            return new JsonResponse(['error' => 'Invalid API key'], 401);
        }

        return null;
    }
}

