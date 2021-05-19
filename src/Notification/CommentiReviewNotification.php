<?php

namespace App\Notification;
use App\Entity\CommentiPubblici;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;

class CommentiReviewNotification extends Notification implements EmailNotificationInterface
{
    private $comment;
    public function __construct(CommentiPubblici $comment)
    {
        $this->comment = $comment;
        parent::__construct('Nuovo feedback postato: '.$comment->getCantieri() );
}
    public function asEmailMessage(EmailRecipientInterface $recipient, string $transport = null): ?EmailMessage
    {
        $message = EmailMessage::fromNotification($this, $recipient, $transport);
        $message->getMessage()
            ->htmlTemplate('emails/comment_notification.html.twig')
            ->context(['comment' => $this->comment])
        ;
        return $message;
    }
}