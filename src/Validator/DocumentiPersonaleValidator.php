<?php

namespace App\Validator;

use App\Entity\DocumentiPersonale;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


class DocumentiPersonaleValidator
{
   
    public static function validate(DocumentiPersonale $documentiPersonale, ExecutionContextInterface $context, $payload)
    {
    
        // controllo presenza titolo e path documento
        if ($documentiPersonale->getDocumentoFile() !== null && $documentiPersonale->getTitolo() === null ) {
            $context->buildViolation('Se scegli un file documento allora devi inserire anche un Titolo (Tipo documento)')
                ->addViolation() ;
        }
        if ($documentiPersonale->getDocumentoFile() === null && $documentiPersonale->getDocumentoPath()  && $documentiPersonale->getTitolo() !== null ) {
            $context->buildViolation('Se inserisci un Titolo nel tipo documento allora devi scegliere un file (pdf o immagini, dalle dimensioni massime di 3MB)')
                ->addViolation() ;
        }

    }
}