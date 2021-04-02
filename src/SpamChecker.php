<?php

namespace App;

use App\Entity\CommentiPubblici;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SpamChecker
{
    /* private $client;
    private $endpoint;

    public function __construct(HttpClientInterface $client, string $akismetKey)
    {
        $this->client = $client;
        $this->endpoint = sprintf('https://%s.rest.akismet.com/1.1/comment-check', $akismetKey);
        // $this->endpoint = sprintf('https://%s.rest.akismet.com/1.1/verify-key', $akismetKey); letto cme esempio
        // https://www.whatsmyip.org/lib/fuspam-akismet-php/
    } */

    /**
     * @return int Spam score: 0: not spam, 1: maybe spam, 2: blatant spam
     *
     * @throws \RuntimeException if the call did not work
     */
    public function getSpamScore(CommentiPubblici $comment, array $context): int
    {
        return 0 ;

        // funziona solo su local , su symfonycloud i messaggi rimangono in stato submitted
        // ho usato il portachiavi di symfony per settare la AKISMET_KEY
        // Messenger che gestisce il cambio di stato mi manda un errore nel log:
        // > symfony tunnel:open     > symfony logs --worker=messages all
        //  Error: "keypair size should be SODIUM_CRYPTO_BOX_KEYPAIRBYTES bytes"
        
        // quindi ho deviso di elimanare la chiamata

      /*   $response = $this->client->request('POST', $this->endpoint, [
            'body' => array_merge($context, [
                // 'blog' => 'https://127.0.0.1:8000',
                // 'blog' => 'https://akismet-guaranteed-spam@example.com',
                'blog' => 'https://vj6pjrmqru-w46y7xz5sxjog.eu.s5y.io',
                'comment_type' => 'comment',
                'comment_author' => $comment->getAuthor(),
                'comment_author_email' => $comment->getEmail(),
                'comment_content' => $comment->getTextComment(),
                'comment_date_gmt' => $comment->getCreatedAt()->format('c'),
                'blog_lang' => 'it',
                'blog_charset' => 'UTF-8',
                'is_test' => true,
            ]),
        ]);

        $headers = $response->getHeaders();
        if ('discard' === ($headers['x-akismet-pro-tip'][0] ?? '')) {
            return 2;
        }

        $content = $response->getContent();
        if (isset($headers['x-akismet-debug-help'][0])) {
            throw new \RuntimeException(sprintf('Non è stato possibile controllare lo spam: %s (%s).', $content, $headers['x-akismet-debug-help'][0]));
        }

        return 'true' === $content ? 1 : 0; */
    }
}
