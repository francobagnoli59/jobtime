<?php

namespace App\Validator;

use App\Entity\Clienti;
use App\Validator\Routine\CodiceFiscaleAziendeValidation;
use App\Validator\Routine\PartitaIvaValidation;
use App\Validator\Routine\CodiceFiscaleValidation;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ClientiValidator
{
    public static function validate(Clienti $clienti,  ExecutionContextInterface $context, $payload)
    {
        if ($clienti->getProvincia()->getCode() === 'XX') {

            $context->buildViolation('Provincia obbligatoria')
                ->addViolation()
            ;
        }
        // Controllo PA - Pubblica Amministrazione
        // Lunghezza SDI 6 char  presenza Partita Iva e Codice Fiscale numerico
        if ($clienti->getTypeCliente() === 'PA') {
            $partitaiva = new PartitaIvaValidation;
            $partitaivaVerify = $partitaiva->verifyPartitaIva($clienti->getPartitaIva());
                if ($partitaivaVerify['Retcode'] === 'ER') {
                    $context->buildViolation($partitaivaVerify['Message'])
                    ->addViolation() ;
             }
            $codicefiscale = new CodiceFiscaleAziendeValidation;
            $codiceFiscaleVerify = $codicefiscale->verifyAziendeFiscalCode($clienti->getFiscalCode());
                if ($codiceFiscaleVerify['Retcode'] === 'ER') {
                    $context->buildViolation($codiceFiscaleVerify['Message'])
                    ->addViolation() ;
             }
             if (strlen($clienti->getCodeSdi()) != 6) {  
            $context->buildViolation('Codice SDI errato, deve contenere 6 caratteri')
            ->addViolation() ;
             }
        } 

        // Controllo PG - Persona Giuridica (Aziende comuni)
        // Lunghezza SDI 7 char presenza Partita Iva e Codice Fiscale numerico
        if ($clienti->getTypeCliente() === 'PG') {
            $partitaiva = new PartitaIvaValidation;
            $partitaivaVerify = $partitaiva->verifyPartitaIva($clienti->getPartitaIva());
                if ($partitaivaVerify['Retcode'] === 'ER') {
                    $context->buildViolation($partitaivaVerify['Message'])
                    ->addViolation() ;
             }
            $codicefiscale = new CodiceFiscaleAziendeValidation;
            $codiceFiscaleVerify = $codicefiscale->verifyAziendeFiscalCode($clienti->getFiscalCode());
                if ($codiceFiscaleVerify['Retcode'] === 'ER') {
                    $context->buildViolation($codiceFiscaleVerify['Message'])
                    ->addViolation() ;
             }
             if (strlen($clienti->getCodeSdi()) != 7) {  
            $context->buildViolation('Codice SDI errato, deve contenere 7 caratteri')
            ->addViolation() ;
             }
        } 
        
        // Controllo EN - Ente No Profit
        // Lunghezza SDI 7 char  nessuna presenza Partita Iva e controllo Codice Fiscale numerico
        if ($clienti->getTypeCliente() === 'EN') {
            if (strlen($clienti->getPartitaIva()) !=0) {  
                $context->buildViolation('Partita Iva non ammessa, gli enti NO PROFIT non hanno partita Iva. Cambiare tipo di azienda se la Partita Iva appartiene al Cliente')
                ->addViolation() ;
            }
            $codicefiscale = new CodiceFiscaleAziendeValidation;
            $codiceFiscaleVerify = $codicefiscale->verifyAziendeFiscalCode($clienti->getFiscalCode());
                if ($codiceFiscaleVerify['Retcode'] === 'ER') {
                    $context->buildViolation($codiceFiscaleVerify['Message'])
                    ->addViolation() ;
             }
             if (strlen($clienti->getCodeSdi()) != 7) {  
            $context->buildViolation('Codice SDI errato, deve contenere 7 caratteri')
            ->addViolation() ;
             }
        } 

        // Controllo DI - Ditta individuale
        // Codice SDI 7 char  presenza Partita Iva e Codice Fiscale alfanumerico
        if ($clienti->getTypeCliente() === 'DI') {
            $partitaiva = new PartitaIvaValidation;
            $partitaivaVerify = $partitaiva->verifyPartitaIva($clienti->getPartitaIva());
                if ($partitaivaVerify['Retcode'] === 'ER') {
                    $context->buildViolation($partitaivaVerify['Message'])
                    ->addViolation() ;
             }
            $codicefiscale = new CodiceFiscaleValidation;
            $codiceFiscaleVerify = $codicefiscale->verifyFiscalCode($clienti->getFiscalCode());
                if ($codiceFiscaleVerify['Retcode'] === 'ER') {
                    $context->buildViolation($codiceFiscaleVerify['Message'])
                    ->addViolation() ;
             }
             if (strlen($clienti->getCodeSdi()) != 7) {  
            $context->buildViolation('Codice SDI errato, deve contenere 7 caratteri')
            ->addViolation() ;
             }
        } 

        // Controllo PF - Persona Fisica
        // Codice SDI (0000000)  Codice Fiscale alfanumerico
        if ($clienti->getTypeCliente() === 'PF') {
            $codicefiscale = new CodiceFiscaleValidation;
            $codiceFiscaleVerify = $codicefiscale->verifyFiscalCode($clienti->getFiscalCode());
            if ($codiceFiscaleVerify['Retcode'] === 'ER') {
                $context->buildViolation($codiceFiscaleVerify['Message'])
                ->addViolation() ;
             }
            if ($clienti->getCodeSdi() != '0000000') {  
            $context->buildViolation('Codice SDI errato, deve essere uguale a 0000000')
            ->addViolation() ;
            }
       } 
        
    }
}