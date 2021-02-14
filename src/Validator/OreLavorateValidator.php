<?php

namespace App\Validator;

use App\Entity\OreLavorate;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


class OreLavorateValidator
{
   
    public static function validate(OreLavorate $orelavorate, ExecutionContextInterface $context, $payload)
    {

    
        // controllo ore registrate
        if (is_numeric($orelavorate->getOreRegistrate()) === false) {

            $context->buildViolation('Il campo Ore registrate deve contenere un numero valido')
                ->addViolation() ;
        } else {
            if ($orelavorate->getOreRegistrate() > 13 ) {
                $context->buildViolation('Per un giorno lavorativo non si possono superare 13 ore')
                ->addViolation() ;
            } 
        }

        
        /* // controllo cantiere
        if ($orelavorate->getCantiere()->getNameJob() === '- da assegnare '.$orelavorate->getAzienda()->getNickName()) {
            $context->buildViolation('Indicare un cantiere valido')
            ->addViolation() ;
        } */

    }
}