<?php

namespace App\EventSubscriber;
use App\Repository\MovieRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;
class TwigEventSubscriber implements EventSubscriberInterface
{
    private $twig;
    private $movieRepository;

    public function __construct(Environment $twig, MovieRepository $movieRepository)
    {
        $this->twig = $twig;
        $this->movieRepository = $movieRepository;
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $this->twig->addGlobal('movies', $this->movieRepository->findAll());
    }

    public static function getSubscribedEvents(): array
    {
        return [ KernelEvents::CONTROLLER => 'onKernelController' ];
    }
}
