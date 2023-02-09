<?php

namespace App\EntityListener;

use App\Entity\Movie;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsEntityListener(event: Events::prePersist, entity: Movie::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Movie::class)]
class MovieListener
{
    public function __construct(
        private SluggerInterface $slugger,
    ) {
    }

    public function prePersist(Movie $movie, LifecycleEventArgs $event)
    {
        $movie->computeSlug($this->slugger);
    }

    public function preUpdate(Movie $movie, LifecycleEventArgs $event)
    {
        $movie->computeSlug($this->slugger);
    }
}