<?php

namespace App\Validator;

use App\Entity\RaccoltaOrePersone;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Doctrine\Common\Collections\ArrayCollection;

class RaccoltaOrePersoneValidator
{
   
    public static function validate(RaccoltaOrePersone $raccoltaOrePersone, ExecutionContextInterface $context, $payload)
    {
        // Controlla Moduli raccolta ore  
        $moduliRaccoltaOreCantieri = new ArrayCollection;
        $moduliRaccoltaOreCantieri = $raccoltaOrePersone->getOreMeseCantieri();
        foreach ($moduliRaccoltaOreCantieri as $oreCantiere) {
            // Controlla piano ore settimanali
            $hourdayarray = $oreCantiere->getOreGiornaliere();
            $anno = $oreCantiere->getRaccoltaOrePersona()->getAnno();
            $mese = $oreCantiere->getRaccoltaOrePersona()->getMese();
            $giorninelmese = cal_days_in_month(CAL_GREGORIAN, intval($mese) , intval($anno));
            $itemDay = 0;
            $title = 'ORE MENSILI: '.$oreCantiere->getCantiere();
            foreach ($hourdayarray as $d) {
                $itemDay++ ;  
                //  if ($itemDay !== 32 ) {
                if ($itemDay <= $giorninelmese ) {
                    if (is_numeric($d)) {
                        if ($d < 0 || $d > 8) {  $context->buildViolation(sprintf('%s le ore %s impostate al giorno %d sono fuori dai limiti consentiti', $title,  $d, $itemDay))
                                                ->addViolation() ; }
                    } else 
                    {  $context->buildViolation(sprintf('%s al giorno %d inserire un numero compreso tra 0 e 8',$title, $itemDay))
                        ->addViolation() ; }
                }
            }
            if ($itemDay > 32 ) {
                $context->buildViolation(sprintf('%s anomalia nel calcolo del numero di giorni mensili, contattare il supporto tecnico.',$title))
                ->addViolation() ;
            }
        }
    }
}