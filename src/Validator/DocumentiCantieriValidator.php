<?php

namespace App\Validator;

use App\Entity\DocumentiCantieri;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


class DocumentiCantieriValidator
{
   
    public static function validate(DocumentiCantieri $documentiCantieri, ExecutionContextInterface $context, $payload)
    {
    
        // controllo presenza titolo e path documento
        if ($documentiCantieri->getDocumentoFile() !== null && $documentiCantieri->getTitolo() === null  && ($documentiCantieri->getTipologia() === 'NUL' || $documentiCantieri->getTipologia() === 'OTH') ) {
            $context->buildViolation('Se scegli un file documento generico allora devi inserire anche un Titolo (oppure seleziona un tipo documento appropriato)')
                ->addViolation() ;
        }
        if ($documentiCantieri->getDocumentoFile() === null && $documentiCantieri->getDocumentoName() === null && ($documentiCantieri->getTitolo() !== null || $documentiCantieri->getTipologia() !== null ) ) {
            $context->buildViolation('Se inserisci un Titolo nel tipo documento allora devi scegliere un file (pdf o immagini, dalle dimensioni massime di 3MB)')
                ->addViolation() ;
        }

    }
}