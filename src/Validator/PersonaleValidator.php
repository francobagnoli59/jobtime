<?php

namespace App\Validator;

use App\Entity\Personale;
use App\Validator\Routine\CodiceFiscaleValidation;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PersonaleValidator
{
    public static function validate(Personale $personale,  ExecutionContextInterface $context, $payload)
    {
        if ($personale->getProvincia()->getCode() === 'XX') {

            $context->buildViolation('Provincia obbligatoria')
                ->addViolation()
            ;
        }

        $codicefiscale = new CodiceFiscaleValidation;
        $codiceFiscaleVerify = $codicefiscale->verifyFiscalCode($personale->getFiscalCode());
        //print_r($codiceFiscaleVerify);
        if ($codiceFiscaleVerify['Retcode'] === 'ER') {
            $context->buildViolation($codiceFiscaleVerify['Message'])
                ->addViolation() ; } 
        else {
                // Codice valido 
                // controlla corrispondenza sesso
                if ($personale->getGender() != $codiceFiscaleVerify['Gender']) {
                 $context->buildViolation('Il sesso non corrisponde al codice fiscale')
                 ->addViolation() ; }
                 else { 
                     // sesso corrisponde, se data di nascita non impostata, viene assegnata
                     if ($personale->getBirthday() === null ) { $personale->setBirthday($codiceFiscaleVerify['Birthday']) ;} 
                     else { 
                         if ($personale->getBirthday() != $codiceFiscaleVerify['Birthday'] ) {
                            $context->buildViolation('La data di nascita non corrisponde al codice fiscale')
                            ->addViolation() ;
                         }
                     }  
                }    
             }
    }
}