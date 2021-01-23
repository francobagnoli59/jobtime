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
    }
}