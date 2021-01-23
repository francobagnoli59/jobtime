<?php

namespace App\Validator;

use App\Entity\Aziende;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class AziendeValidator
{
    public static function validate(Aziende $aziende, ExecutionContextInterface $context, $payload)
    {
        if ($aziende->getProvincia()->getCode() === 'XX') {

            $context->buildViolation('Provincia obbligatoria')
                ->addViolation()
            ;
        }
    }
}