<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class JsonFailedDecodingEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [ExceptionEvent::class => 'onExceptionEvent'];
    }

    public function onExceptionEvent(ExceptionEvent $event): void
    {
        $isHttpException = $event->getThrowable() instanceof HttpExceptionInterface;
        $isJsonException = $event->getThrowable()->getPrevious() instanceof JsonException;

        if ($isHttpException === false) {
            return;
        }

        if ($isJsonException === false) {
            return;
        }

        $jsonDecodingFailedException = $event->getThrowable()->getPrevious();

        $event->setResponse(
            new JsonResponse(
                ['errors' => [
                    [
                        'status' => Response::HTTP_BAD_REQUEST,
                        'title' => $jsonDecodingFailedException->getMessage(),
                    ]
                ]],
                Response::HTTP_BAD_REQUEST,
            )
        );
    }
}
