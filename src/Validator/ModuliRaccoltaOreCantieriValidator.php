<?php

namespace App\Validator;

use App\Entity\ModuliRaccoltaOreCantieri;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


class ModuliRaccoltaOreCantieriValidator
{
   
    public static function validate(ModuliRaccoltaOreCantieri $moduliRaccoltaOreCantieri, ExecutionContextInterface $context, $payload)
    {
       
        // Controlla piano ore settimanali
        $hourdayarray = $moduliRaccoltaOreCantieri->getOreGiornaliere();
        $itemDay = 0;
        $title = 'ORE MENSILI: '.$moduliRaccoltaOreCantieri->getCantiere();
        foreach ($hourdayarray as $d) {
            $itemDay++ ;
            if ($itemDay !== 32 ) {
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