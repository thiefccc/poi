<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        // Function which will be called on KernelEvent = Exception
        return array(KernelEvents::EXCEPTION => 'onKernelException');
    }

    // Will throw exception in JSON
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $e = $event->getException();

        $response = new JsonResponse(
            ["error" => [
                "file" => $e->getFile(),
                "line" => $e->getLine(),
                "message" => $e->getMessage()/*,
                "file" => $e->getFile(),
                "trace" => $e->getTraceAsString()*/
            ]]);

        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        $event->setResponse($response);
    }
}