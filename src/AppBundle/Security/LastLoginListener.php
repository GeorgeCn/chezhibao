<?php

namespace AppBundle\Security;

use FOS\UserBundle\EventListener\LastLoginListener as BaseListener;
use FOS\UserBundle\Event\UserEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use FOS\UserBundle\Model\UserInterface;


class LastLoginListener extends BaseListener
{
    public function onImplicitLogin(UserEvent $event)
    {
        $user = $event->getUser();
        $user->setLastIp($event->getRequest()->getClientIp());

        parent::onImplicitLogin($event);
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        if ($user instanceof UserInterface) {
            $user->setLastIp($event->getRequest()->getClientIp());
        }

        parent::onSecurityInteractiveLogin($event);
    }
}