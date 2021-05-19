<?php

namespace App\Controller;

use App\Entity\CommentiPubblici;
use App\Message\CommentiMessage;
use App\Notification\CommentiAcceptNotification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// use Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
// use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\Registry;
use Twig\Environment;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    private $twig;
    private $entityManager;
    private $bus;
    private $notifier;

    public function __construct(Environment $twig, EntityManagerInterface $entityManager, MessageBusInterface $bus, NotifierInterface $notifier )
    {
        $this->twig = $twig;
        $this->entityManager = $entityManager;
        $this->bus = $bus;
        $this->notifier = $notifier;
    }

    /**
     * @Route("/comment/review/{id}", name="review_comment")
     */
    public function reviewComment(Request $request, CommentiPubblici $comment, Registry $registry): Response
    {
        $accepted = !$request->query->get('reject');

        $machine = $registry->get($comment);
        if ($machine->can($comment, 'publish')) {
            $transition = $accepted ? 'publish' : 'reject';
        } elseif ($machine->can($comment, 'publish_ham')) {
            $transition = $accepted ? 'publish_ham' : 'reject_ham';
        } else {
            return new Response('Commento già processato oppure non si trova nello stato logico valido.');
        }

        $machine->apply($comment, $transition);
        $this->entityManager->flush();

        if ($accepted) {
            $this->bus->dispatch(new CommentiMessage($comment->getId()));

            $this->notifier->send(new CommentiAcceptNotification($comment),
            ...$this->notifier->getAdminRecipients());
        } 

        return $this->render('admin/review.html.twig', [
            'transition' => $transition,
            'comment' => $comment,
        ]);
    }

      
}

