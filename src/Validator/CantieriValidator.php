<?php

namespace App\Validator;

use App\Entity\Cantieri;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Doctrine\Common\Collections\ArrayCollection;

class CantieriValidator
{
    public static function validate(Cantieri $cantieri, ExecutionContextInterface $context, $payload)
    {
        if ($cantieri->getProvincia()->getCode() === 'XX') {

            $context->buildViolation('Provincia obbligatoria')
                ->addViolation()
            ;
        }

        // Controllo coerenza dati amministrazione pubblica
        if ($cantieri->getCliente() !== null) {
            if ($cantieri->getCliente()->getTypeCliente() === 'PA') {

                if ($cantieri->getTypeOrderPA() != 'C' && $cantieri->getTypeOrderPA() != 'E'  && $cantieri->getTypeOrderPA() != 'O') {

                    $context->buildViolation('Cliente Pubblica Amministrazione, ma tipologia Appanto non indicata.')
                    ->addViolation() ;
                }
            } 
            // Cliente non amministrazione pubblica
        else {  

                if ($cantieri->getTypeOrderPA() === 'C' || $cantieri->getTypeOrderPA() ==='E' ) {
                    $context->buildViolation('Indicato: Appalto per Pubblica Amministrazione, ma cliente non Pubblica Amministrazione')
                        ->addViolation() ;
                }

                if (strlen($cantieri->getCodiceCIG()) > 0 ) {
                    $context->buildViolation('Il codice CIG è riservato agli Appalti Pubblica Amministrazione.')
                    ->addViolation() ;
                }

                if (strlen($cantieri->getCodiceCUP()) > 0) {
                    $context->buildViolation('Il codice CUP è riservato agli Appalti Pubblica Amministrazione.')
                    ->addViolation() ;
                }
            }
        }

            // se contratto PA
            if ($cantieri->getTypeOrderPA() === 'C' || $cantieri->getTypeOrderPA() ==='E' ) {

                if (strlen($cantieri->getNumDocumento()) === 0 ) {
                    $context->buildViolation('Appalto Pubblica Amministrazione, inserire numero documento.')
                    ->addViolation() ;
                }
                if ( $cantieri->getDateDocumento() === null) {
                    $context->buildViolation('Appalto Pubblica Amministrazione, inserire una data documento.')
                    ->addViolation() ;
                } 
                if (strlen($cantieri->getCodiceCIG()) === 0 &&  strlen($cantieri->getCodiceCUP()) === 0) {
                    $context->buildViolation('Appalto Pubblica Amministrazione, inserire un codice CIG o un codice CUP.')
                    ->addViolation() ;
                }
                if ($cantieri->getCliente() === null) {
                    if ( $cantieri->getcodiceIPA() === null) {
                        $context->buildViolation('Appalto Pubblica Amministrazione, inserire Identificativo univoco Ufficio.')
                        ->addViolation() ;
                    } 
                }
                
            }

            if ($cantieri->getTypeOrderPA() === 'O') {

                if (strlen($cantieri->getNumDocumento()) === 0 ) {
                    $context->buildViolation('Con ordine di acquisto selezionato, inserire un numero documento.')
                    ->addViolation() ;
                }
                if ( $cantieri->getDateDocumento() === null) {
                    $context->buildViolation('Con ordine di acquisto selezionato, inserire una data documento.')
                    ->addViolation() ;
                } 
                
            }
        
        // Controlla documenti 
        $documentiPA = false;
        $documentiCantieri = new ArrayCollection;
        $documentiCantieri = $cantieri->getDocumentiCantiere();
            foreach ($documentiCantieri as $documento) {
                 // controllo presenza titolo e path documento
                if ($documento->getDocumentoFile() !== null && $documento->getTitolo() === null  && ($documento->getTipologia() === 'NUL' || $documento->getTipologia() === 'OTH') ) {
                    $context->buildViolation('Se scegli un file documento generico allora devi inserire anche un Titolo (oppure seleziona un tipo documento appropriato)')
                        ->addViolation() ;
                }
                if ($documento->getDocumentoFile() === null && $documento->getDocumentoName() === null && ($documento->getTitolo() !== null || $documento->getTipologia() !== null ) ) {
                    $context->buildViolation('Se inserisci un Titolo nel tipo documento allora devi scegliere un file (pdf o immagini, dalle dimensioni massime di 3MB)')
                        ->addViolation() ;
                }
                // 
                $tipo = $documento->getTipologia();
                switch ($tipo) {
                    case "CPA":
                    case "DPA":    
                        $documentiPA = true;
                        break;
                }
            }

            // se PA deve essere caricato alemeno un documento PA
            if ($cantieri->getTypeOrderPA() === 'C' || $cantieri->getTypeOrderPA() ==='E' ) {
                if ($documentiPA === false) {
                    $context->buildViolation('Per un cantiere destinato ad un ente pubblico è necessario caricare il Contratto e/o la Determina.')
                    ->addViolation() ;
                }
            }

    }
}