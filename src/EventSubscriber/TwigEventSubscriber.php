<?php

namespace App\EventSubscriber;

use App\Repository\CantieriRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Twig\Environment;


class TwigEventSubscriber implements EventSubscriberInterface
{
    private $twig;
    private $cantieriRepository;

    public function __construct(Environment $twig, CantieriRepository $cantieriRepository)
{
        $this->twig = $twig;
        $this->cantieriRepository = $cantieriRepository; 
}

    public function onControllerEvent(ControllerEvent $event)
    {
        $this->twig->addGlobal('listcantieri', $this->cantieriRepository->findBy(['isPublic' => true], ['dateStartJob' => 'DESC']));
    }

    public static function getSubscribedEvents()
    {
        return [
            ControllerEvent::class => 'onControllerEvent',
        ];
    }
}
