<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class PayloadFailedValidationEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [ExceptionEvent::class => 'onExceptionEvent'];
    }

    public function onExceptionEvent(ExceptionEvent $event): void
    {
        $isHttpException = $event->getThrowable() instanceof HttpExceptionInterface;
        $isValidationFailedException = $event->getThrowable()->getPrevious() instanceof ValidationFailedException;

        if ($isHttpException === false) {
            return;
        }

        if ($isValidationFailedException === false) {
            return;
        }

        /**
         * @var ValidationFailedException $validationException
         */
        $validationException = $event->getThrowable()->getPrevious();
        $errors = [];
        foreach ($validationException->getViolations() as $violation) {
            $errors[] = [
                'status' => Response::HTTP_BAD_REQUEST,
                'title' => $violation->getMessage(),
                'source' => [
                    'pointer' => sprintf('/%s', $violation->getPropertyPath())
                ]
            ];
        }

        $event->setResponse(
            new JsonResponse(
                ['errors' => $errors],
                Response::HTTP_BAD_REQUEST,
            )
        );
    }
}
