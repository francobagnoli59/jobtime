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
    private $adminEmail;
    public function __construct(CommentiPubblici $comment, string $adminEmail)
    {
        $this->comment = $comment;
        $this->adminEmail = $adminEmail;
        parent::__construct('Confermato il feedback relativo al progetto: '.$comment->getCantieri() );
}
    public function asEmailMessage(EmailRecipientInterface $recipient, string $transport = null): ?EmailMessage
    {
        $message = EmailMessage::fromNotification($this, $recipient, $transport);
        $message->getMessage()
            ->htmlTemplate('emails/comment_accept_notification.html.twig')
            ->context(['comment' => $this->comment])
            ->to($this->comment->getEmail())
            ->bcc($this->adminEmail)
        ;
        return $message;
    }
}