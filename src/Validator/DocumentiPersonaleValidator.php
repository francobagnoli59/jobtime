<?php

namespace App\Validator;

use App\Entity\DocumentiPersonale;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


class DocumentiPersonaleValidator
{
   
    public static function validate(DocumentiPersonale $documentiPersonale, ExecutionContextInterface $context, $payload)
    {
       
        // controllo presenza titolo e path documento
        if ($documentiPersonale->getDocumentoFile() !== null && $documentiPersonale->getTitolo() === null && ($documentiPersonale->getTipologia() === 'NUL' || $documentiPersonale->getTipologia() === 'OTH') ) {
            $context->buildViolation('Se scegli un file documento generico allora devi inserire anche un Titolo (oppure seleziona un tipo documento appropriato)')
                ->addViolation() ;
        }
        if ($documentiPersonale->getDocumentoFile() === null && $documentiPersonale->getDocumentoPath() === null && ($documentiPersonale->getTitolo() !== null || $documentiPersonale->getTipologia() !== null ) ) {
            $context->buildViolation('Se inserisci un Tipo o un Titolo documento allora devi scegliere un file (pdf o immagini, dalle dimensioni massime di 3MB)')
                ->addViolation() ;
        }

    }
}