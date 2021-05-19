<?php

namespace App\Notification;
use App\Entity\CommentiPubblici;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;

class CommentiAcceptNotification extends Notification implements EmailNotificationInterface
{
    private $comment;
    public function __construct(CommentiPubblici $comment)
    {
        $this->comment = $comment;
        parent::__construct('Confermato il feedback relativo al progetto: '.$comment->getCantieri() );
}
    public function asEmailMessage(EmailRecipientInterface $recipient, string $transport = null): ?EmailMessage
    {
        $message = EmailMessage::fromNotification($this, $recipient, $transport);
        $message->getMessage()
            ->htmlTemplate('emails/comment_accept_notification.html.twig')
            ->context(['comment' => $this->comment])
            ->to($this->comment->getEmail())
            ->bcc($recipient->getEmail())
        ;
        return $message;
    }
}