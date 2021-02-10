<?php

namespace App\Validator;

use App\Entity\FestivitaAnnuali;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class FestivitaAnnualiValidator
{
    public static function validate(FestivitaAnnuali $festivitaAnnuali, ExecutionContextInterface $context, $payload)
    {
        if (count($festivitaAnnuali->getDateFestivita()) === 0 ){

            $context->buildViolation('Inserire le festività annuali')
                ->addViolation() ;
        } else {
            
            $item = 1;
            foreach ($festivitaAnnuali->getDateFestivita() as $ggmm) {
                if (strlen($ggmm) >= 4) {

                    $gg = substr($ggmm, 0, 2);
                    $mm = substr($ggmm, 2, 2);
                   
                    if (is_numeric($gg)) {
                        if ($gg < 1 || $gg > 31 ) {
                            $message = 'Il giorno della data della festività di %s all\'elemento %d non è compreso tra 1 e 31' ;
                            $festa = substr($ggmm, 4);
                            $message = sprintf($message, $festa, $item);
                            $context->buildViolation($message)->addViolation();
                        }
                    } else { 
                            $message = 'Il giorno della data della festività di %s all\'elemento %d non è numerico' ;
                            $festa = substr($ggmm, 0);
                            $message = sprintf($message, $festa, $item);
                            $context->buildViolation($message)->addViolation();
                      }

                    if (is_numeric($mm)) {
                        if ($mm < 1 || $mm > 12 ) {
                            $message = 'Il mese della data della festività di %s all\'elemento %d non è compreso tra 1 e 12' ;
                            $festa = substr($ggmm, 4);
                            $message = sprintf($message, $festa, $item);
                            $context->buildViolation($message)->addViolation();
                        }
                    } else {
                            $message = 'Il mese della data della festività di %s all\'elemento %d non è numerico' ;
                            $festa = substr($ggmm, 2);
                            $message = sprintf($message, $festa, $item);
                            $context->buildViolation($message)->addViolation();

                      }

                } else
                {
                    $message = 'La data della festività all\'elemento %d non è valida' ;
                    $message = sprintf($message, $item);
                    $context->buildViolation($message)->addViolation();

                }
                $item += 1;
            }
      
        }
    }
}