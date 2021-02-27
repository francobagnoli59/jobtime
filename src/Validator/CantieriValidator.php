<?php

namespace App\Validator;

use App\Entity\Cantieri;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class CantieriValidator
{
    public static function validate(Cantieri $cantieri, ExecutionContextInterface $context, $payload)
    {
        if ($cantieri->getProvincia()->getCode() === 'XX') {

            $context->buildViolation('Provincia obbligatoria')
                ->addViolation()
            ;
        }

        // Controllo coerenza dati amministrazione pubblica
        if ($cantieri->getCliente() !== null) {
            if ($cantieri->getCliente()->getTypeCliente() === 'PA') {

                if ($cantieri->getTypeOrderPA() != 'C' && $cantieri->getTypeOrderPA() != 'E'  && $cantieri->getTypeOrderPA() != 'O') {

                    $context->buildViolation('Cliente Pubblica Amministrazione, ma tipologia Appanto non indicata.')
                    ->addViolation() ;
                }
            
                if ($cantieri->getTypeOrderPA() === 'C' || $cantieri->getTypeOrderPA() ==='E' ||  $cantieri->getTypeOrderPA() === 'O') {

                    if (strlen($cantieri->getNumDocumento()) === 0 ) {
                        $context->buildViolation('Appalto Pubblica Amministrazione, inserire numero documento.')
                        ->addViolation() ;
                    }
                    if ( $cantieri->getDateDocumento() === null) {
                        $context->buildViolation('Appalto Pubblica Amministrazione, inserire una data documento.')
                        ->addViolation() ;
                    } 
                    if (strlen($cantieri->getCodiceCIG()) === 0 &&  strlen($cantieri->getCodiceCUP()) === 0) {
                        $context->buildViolation('Appalto Pubblica Amministrazione, inserire un codice CIG o un codice CUP.')
                        ->addViolation() ;
                    }
                }
            } 

            // Cliente non amministrazione pubblica
            else {  

                if ($cantieri->getTypeOrderPA() === 'C' || $cantieri->getTypeOrderPA() ==='E' ) {
                    $context->buildViolation('Appalto per Pubblica Amministrazione, ma cliente non Pubblica Amministrazione')
                        ->addViolation() ;
                }

                if (strlen($cantieri->getCodiceCIG()) > 0 ) {
                    $context->buildViolation('Il codice CIG è riservato agli Appalti Pubblica Amministrazione.')
                    ->addViolation() ;
                }

                if (strlen($cantieri->getCodiceCUP()) > 0) {
                    $context->buildViolation('Il codice CUP è riservato agli Appalti Pubblica Amministrazione.')
                    ->addViolation() ;
                }
            }
        }
    }
}