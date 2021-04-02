<?php

namespace App\Controller;

use App\Entity\Cantieri;
use App\Entity\CommentiPubblici;
use App\Repository\CommentiPubbliciRepository;
use App\Repository\CantieriRepository;
use App\Form\CommentiType;
use App\Message\CommentiMessage;
// use App\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class CantieriController extends AbstractController
{   
    private $twig;
    private $entityManager;
    private $bus;

    public function __construct(Environment $twig, EntityManagerInterface $entityManager, MessageBusInterface $bus)
    {
    $this->twig = $twig; 
    $this->entityManager = $entityManager;
    $this->bus = $bus;
    }


    /**
     * @Route("/", name="homepage")
     */
    public function index( CantieriRepository $cantieriRepository): Response
    {
        
        return new Response($this->twig->render('cantieri/index.html.twig', [
            'cantieri' => $cantieriRepository->findBy(['isPublic' => true], ['dateStartJob' => 'DESC']),
            //  'cantieri' => $cantieriRepository->findAll(),
        ]));

        /* return new Response(<<<EOF  
        <html>
            <body>
                <h1>Cantieri al lavoro</h1>
                <img src="/images/under-construction.gif" />
            </body>
        </html>
        EOF  );       */
    }


    /**
     * @Route("/cantieri/{nameJob}", name="cantieri")
     */
     public function show(Request $request, Cantieri $cantieri, CommentiPubbliciRepository $commentiPubbliciRepository, string $photoDirFeedback): Response
    {
        $commento = new CommentiPubblici();
        $form = $this->createForm(CommentiType::class, $commento);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $commento->setCantieri($cantieri);
            if ($photo = $form['photo']->getData()) {
                $filename = bin2hex(random_bytes(6)).'.'.$photo->guessExtension();
                try {
                    $photo->move($photoDirFeedback, $filename);
                } catch (FileException $e) {
                    // messaggio errore
                    $this->addFlash('danger', 'Non è stato possibile caricare la foto, per cortesia riprova.');
                }
                $commento->setPhotoFilename($filename);
            }
            $this->entityManager->persist($commento);
            $this->entityManager->flush();

            $context = [
                'user_ip' => $request->getClientIp(),
                'user_agent' => $request->headers->get('user_agent'),
                'referrer' => $request->headers->get('referrer'),
                'permalink' => $request->getUri(),
            ];
/*             if (2 === $spamChecker->getSpamScore($commento, $context)) {
                throw new \RuntimeException('Spam palese, va via!');
            }
            $this->entityManager->flush(); */
            $this->bus->dispatch(new CommentiMessage($commento->getId(), $context));
            return $this->redirectToRoute('cantieri', ['nameJob' => $cantieri->getNameJob()]);
        }
        
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $commentiPubbliciRepository->getCommentPaginator($cantieri, $offset);
        

        return new Response($this->twig->render('cantieri/show.html.twig', [
            'cantieri' => $cantieri,
            'commenti' => $paginator,
            'previous' => $offset - $commentiPubbliciRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + $commentiPubbliciRepository::PAGINATOR_PER_PAGE),
            'commento_form' => $form->createView(),
             ])); 
        }   

}
