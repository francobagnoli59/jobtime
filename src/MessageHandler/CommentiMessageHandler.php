<?php

namespace App\MessageHandler;
use App\Message\CommentiMessage;
use App\Repository\CommentiPubbliciRepository;
use App\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CommentiMessageHandler implements MessageHandlerInterface
{
    private $spamChecker;
    private $entityManager;
    private $commentRepository;
    public function __construct(EntityManagerInterface $entityManager, SpamChecker $spamChecker, CommentiPubbliciRepository $commentRepository)
    {
        $this->entityManager = $entityManager;
        $this->spamChecker = $spamChecker;
        $this->commentRepository = $commentRepository;
}
    public function __invoke(CommentiMessage $message)
    {
        $comment = $this->commentRepository->find($message->getId());
        if (!$comment) {
            return; }
        if (2 === $this->spamChecker->getSpamScore($comment,
            $message->getContext())) {
            $comment->setState('spam'); } 
            else{  $comment->setState('published');
        }
        $this->entityManager->flush();
    }
}