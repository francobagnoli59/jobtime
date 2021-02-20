<?php

namespace App\Validator;

use App\Entity\ImportPersonale;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ImportPersonaleValidator
{
    public static function validate(ImportPersonale $importPersonale, ExecutionContextInterface $context, $payload)
    {
        if ($importPersonale->getPathImport() !== null && $importPersonale->getPathImport() !== '' ) {

            if (($importPersonale->getNota() === null) || ($importPersonale->getNota() == '')){

                $context->buildViolation('Descrivere brevemente il contenuto del file da importare')
                    ->addViolation()
                ;
            }

            // verifica estensione
            $path = $importPersonale->getPathImport();
            if ( substr($path, strpos($path, ".")+1) !== 'xlsx' && substr($path, strpos($path, ".")+1) !== 'XLSX')  {
                $context->buildViolation('Selezionare un file Excel .xlsx ')
                ->addViolation()
            ;

            }

        }
       
    }
}