<?php

namespace App\Validator;

use App\Entity\ImportCantieri;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ImportCantieriValidator
{
    public static function validate(ImportCantieri $importCantieri, ExecutionContextInterface $context, $payload)
    {
        if ($importCantieri->getPathImport() !== null && $importCantieri->getPathImport() !== '' ) {

            if (($importCantieri->getNota() === null) || ($importCantieri->getNota() == '')){

                $context->buildViolation('Descrivere brevemente il contenuto del file da importare')
                    ->addViolation()
                ;
            }

            // verifica estensione
            $path = $importCantieri->getPathImport();
            if ( substr($path, strpos($path, ".")+1) !== 'xlsx' && substr($path, strpos($path, ".")+1) !== 'XLSX')  {
                $context->buildViolation('Selezionare un file Excel .xlsx ')
                ->addViolation()
            ;

            }

        }
       
    }
}