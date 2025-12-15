<?php

namespace App\Infrastructure\Middleware\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface RequestMiddleware
{
    /**
     * Return a Response to stop the request,
     * or null to continue.
     */
    public function process(Request $request): ?Response;
}
