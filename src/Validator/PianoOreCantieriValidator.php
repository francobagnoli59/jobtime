<?php

namespace App\Validator;

use App\Entity\PianoOreCantieri;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


class PianoOreCantieriValidator
{
   
    public static function validate(PianoOreCantieri $pianoorecantieri, ExecutionContextInterface $context, $payload)
    {

    
        // controllo ore registrate
        if (is_numeric($pianoorecantieri->getOrePreviste()) === false) {

            $context->buildViolation('Il campo Ore registrate deve contenere un numero valido')
                ->addViolation() ;
        } else {
            if ($pianoorecantieri->getOrePreviste() > 8 ) {
                $context->buildViolation('Per un giorno lavorativo non si possono superare 8 ore')
                ->addViolation() ;
            } 
        }

    }
}