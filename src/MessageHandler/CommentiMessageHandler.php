<?php

namespace App\MessageHandler;
use App\Message\CommentiMessage;
use App\Repository\CommentiPubbliciRepository;
use App\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\WorkflowInterface;

class CommentiMessageHandler implements MessageHandlerInterface
{
    private $spamChecker;
    private $entityManager;
    private $commentRepository;
    private $bus;
    private $workflow;
    private $mailer;
    private $adminEmail;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, SpamChecker $spamChecker, CommentiPubbliciRepository $commentRepository,
    MessageBusInterface $bus,  WorkflowInterface $commentStateMachine, MailerInterface $mailer, string $adminEmail, LoggerInterface $logger = null)
    {
        $this->entityManager = $entityManager;
        $this->spamChecker = $spamChecker;
        $this->commentRepository = $commentRepository;
        $this->bus = $bus;
        $this->workflow = $commentStateMachine;
        $this->mailer = $mailer;
        $this->adminEmail = $adminEmail;
        $this->logger = $logger;
}
    public function __invoke(CommentiMessage $message)
    {
        $comment = $this->commentRepository->find($message->getId());
        if (!$comment) {
            return; }

     /*    if (2 === $this->spamChecker->getSpamScore($comment,
            $message->getContext())) {
            $comment->setState('spam'); } 
            else{  $comment->setState('published');
        }
        $this->entityManager->flush(); */
        if ($this->workflow->can($comment, 'accept')) {
            $score = $this->spamChecker->getSpamScore($comment, $message->getContext()) ;
            $transition = 'accept' ;

            if ( 2 === $score ) {
                $transition = 'reject_spam' ;
            } elseif ( 1 === $score ) {
                $transition = 'may_be_spam' ;
            }
            $this->workflow->apply($comment, $transition);
            $this->entityManager->flush();
            $this->bus->dispatch($message);

            } elseif ($this->workflow->can($comment, 'publish') || $this->workflow->can($comment, 'publish_ham')) {
            /* $this->workflow->apply($comment, $this->workflow->can($comment, 'publish') ? 'publish' : 'publish_ham');
            $this->entityManager->flush(); */
            $this->mailer->send((new NotificationEmail())
            ->subject('Pubblicato un nuovo commento')
            ->htmlTemplate('emails/comment_notification.html.twig')
            ->from($this->adminEmail)
            ->to($this->adminEmail)            
            ->context(['comment' => $comment])
            );
            } elseif ($this->logger) {
            $this->logger->debug('Messaggio di commento pubblico non accettato', ['comment' => $comment->getId(), 'state' => $comment->getState()]);
        }

    }
}