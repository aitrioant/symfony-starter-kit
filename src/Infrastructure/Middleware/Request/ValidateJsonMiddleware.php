<?php

namespace App\Infrastructure\Middleware\Request;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ValidateJsonMiddleware implements RequestMiddleware
{
    public function process(Request $request): ?JsonResponse
    {
        if (!$request->isMethodCacheable() && $request->getContentTypeFormat() === 'json') {

            $content = $request->getContent();

            if ($content === '') {
                return new JsonResponse(['error' => 'Empty JSON body'], 400);
            }

            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return new JsonResponse(['error' => 'Malformed JSON'], 400);
            }

            // Inject decoded JSON into request->request (like Symfony forms do)
            $request->request->replace($data);
        }

        return null;
    }
}
