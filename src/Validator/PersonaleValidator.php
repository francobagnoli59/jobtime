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
        // Controlla piano ore settimanali
        $hourdayarray = $personale->getPlanHourWeek();
        $totday = 0;
        $title = 'PIANO ORE SETTIMANALI:';
        foreach ($hourdayarray as $d) {
            $totday++ ;
            if (is_numeric($d)) {
                if ($d < 0 || $d > 8) {  $context->buildViolation(sprintf('%s le ore %s impostate al %d^item sono fuori dai limiti consentiti', $title,  $d, $totday))
                                         ->addViolation() ; }
            } else 
            {  $context->buildViolation(sprintf('%s al %d^item inserire un numero compreso tra 0 e 8',$title, $totday))
                ->addViolation() ; }
        }
        if ($totday != 7 ) {
            $context->buildViolation(sprintf('%s inserire 7 item rispettivamente dal lunedì alla domenica',$title, $totday))
            ->addViolation() ;
        }
        
        // Controlla data scadenza contratto se tipo contratto D / T
        if ($personale->getTipoContratto() === 'D' || $personale->getTipoContratto() === 'T'  ) {
            if ($personale->getScadenzaContratto() === null) {
                $context->buildViolation('Per tipo contratti a tempo Determinato o Stagionali occorre indicare la data di scadenza contratto di lavoro')
                ->addViolation() ;
            }
        }

        // Controlla presenza mansione se invalido
        if ($personale->getIsInvalid() === true &&  $personale->getMansione() === null ) {
            $context->buildViolation('Per le persone diversamente abili è obbligatorio indicare la mansione lavorativa.')
            ->addViolation() ;
        } 

        // Controlla data scadenza visita medica
        if ($personale->getUltimaVisitaMedica() !== null && $personale->getScadenzaVisitaMedica() === null ){
            $context->buildViolation('Se già eseguita la visita medica, occorre indicare anche la scadenza della prossima visita.')
            ->addViolation() ;
        } 
    }
}