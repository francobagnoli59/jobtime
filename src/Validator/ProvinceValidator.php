<?php

namespace App\Validator;

use App\Entity\Province;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ProvinceValidator
{
    public static function validate(Province $province, ExecutionContextInterface $context, $payload)
    {
        if (($province->getCode() === 'XX') && ($province->getName() != '- da assegnare')){

            $context->buildViolation('Dato non modificabile')
                ->addViolation()
            ;
        }
    }
}