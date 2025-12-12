<?php

namespace App\Infrastructure\Subscriber;

use App\Infrastructure\Middleware\Request\RequestMiddleware;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class RequestMiddlewareSubscriber implements EventSubscriberInterface
{
    /** @var iterable<RequestMiddleware> */
    private iterable $middlewares;

    public function __construct(iterable $middlewares)
    {
        $this->middlewares = $middlewares;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.request' => ['onKernelRequest', 10], // priority > router
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            return;
        }

        foreach ($this->middlewares as $middleware) {
            $response = $middleware->process($request);

            if ($response !== null) {
                $event->setResponse($response);
                return; // Stop the pipeline
            }
        }
    }
}
