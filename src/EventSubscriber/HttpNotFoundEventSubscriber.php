<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HttpNotFoundEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [ExceptionEvent::class => 'onExceptionEvent'];
    }

    public function onExceptionEvent(ExceptionEvent $event): void
    {
        $isHttpException = $event->getThrowable() instanceof HttpExceptionInterface;
        $isHttpNotFoundException = $event->getThrowable() instanceof NotFoundHttpException;

        if ($isHttpException === false) {
            return;
        }

        if ($isHttpNotFoundException === false) {
            return;
        }

        $event->setResponse(
            new JsonResponse(
                ['errors' => [
                    [
                        'status' => Response::HTTP_NOT_FOUND,
                        'title' => 'Object not found'
                    ]
                ]],
                Response::HTTP_NOT_FOUND,
            )
        );
    }
}
