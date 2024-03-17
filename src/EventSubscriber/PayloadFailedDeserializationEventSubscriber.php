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

class PayloadFailedDeserializationEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [ExceptionEvent::class => 'onExceptionEvent'];
    }

    public function onExceptionEvent(ExceptionEvent $event): void
    {
        $isHttpException = $event->getThrowable() instanceof HttpExceptionInterface;
        $isDeserializationException = $event->getThrowable()->getPrevious() instanceof NotEncodableValueException;

        if ($isHttpException === false) {
            return;
        }

        if ($isDeserializationException === false) {
            return;
        }

        $deserializationFailedException = $event->getThrowable()->getPrevious();

        $event->setResponse(
            new JsonResponse(
                ['errors' => [
                    [
                        'status' => Response::HTTP_BAD_REQUEST,
                        'title' => $deserializationFailedException->getMessage(),
                    ]
                ]],
                Response::HTTP_BAD_REQUEST,
            )
        );
    }
}
