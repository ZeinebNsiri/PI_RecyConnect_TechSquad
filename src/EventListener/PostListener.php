<?php

namespace App\EventListener;

use App\Entity\Post;
use App\Service\BadWordFilter;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class PostListener
{
    private BadWordFilter $badWords;

    public function __construct(BadWordFilter $badWords)
    {
        $this->badWords = $badWords;
    }

    public function prePersist(PrePersistEventArgs $event): void
    {
        $entity = $event->getObject();

        if (!$entity instanceof Post) {
            return; // Ne fait rien si ce n'est pas un Post
        }

        $filteredContent = $this->badWords->filterText($entity->getContenu());
        $entity->setContenu($filteredContent);
    }

    
}

