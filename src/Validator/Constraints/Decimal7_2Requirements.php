<?php
namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Annotation
 */
class Decimal7_2Requirements extends Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new Assert\Length(['max' => 5]),
            new Assert\GreaterThanOrEqual(0),
        //    new Assert\LessThanOrEqual(999)
        ];
    }
}
