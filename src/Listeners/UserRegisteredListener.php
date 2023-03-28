<?php

namespace App\Listeners;

use App\Event\UserRegisteredEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Service\NotificationService;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class UserRegisteredListener implements EventSubscriberInterface
{
    public function __construct(
        private NotificationService $notificationService,
        private ContainerBagInterface $containerBag
    ) {
    }

    public function onUserRegistered(UserRegisteredEvent $event): void
    {
        $this->notificationService->sendSystemMessage(
            "Welcome to our platform",
            $event->getUser()->getId(),
            $this->containerBag->get('notification_app_url')
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserRegisteredEvent::NAME => 'onUserRegistered',
        ];
    }
}
