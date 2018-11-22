<?php

namespace AppBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use AppBundle\Traits\ContainerAwareTrait;

class OpenApiSubscriber implements EventSubscriberInterface
{
    use ContainerAwareTrait;

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 255],
            KernelEvents::RESPONSE => 'onResponse',
        ];
    }

    public function onRequest(GetResponseEvent $e)
    {
        if (!$e->isMasterRequest()) {
            return;
        }
        $request = $e->getRequest();
        $method = $request->getMethod();
        $origin = $request->getSchemeAndHttpHost();
        $requestHeaders = $request->headers->get('Access-Control-Request-Headers', '');

        if ($method === Request::METHOD_OPTIONS) {
            $response = Response::create();
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT, DELETE');
            $response->headers->set('Access-Control-Allow-Headers', $requestHeaders);
            $response->headers->set('Access-Control-Max-Age', 1728000);
            $response->headers->set('Access-Control-Allow-Credentials', "true");
            $e->setResponse($response);

            return;
        }
    }

    public function onResponse(FilterResponseEvent $e)
    {
        if (!$e->isMasterRequest()) {
            return;
        }
        $request = $e->getRequest();
        $path = $request->getPathInfo();
        $origin = $request->headers->get('Origin', '');
        $response = $e->getResponse();

        if (strpos($path, '/openapi') === false) {
            return;
        }

        $response->headers->set('Access-Control-Allow-Origin', $origin);
        $response->headers->set('Access-Control-Max-Age', 1728000);
        $response->headers->set('Access-Control-Allow-Credentials', "true");
    }
}
