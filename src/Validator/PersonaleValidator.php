<?php

namespace App\Validator;

use App\Entity\Personale;
use App\Validator\Routine\CodiceFiscaleValidation;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Doctrine\Common\Collections\ArrayCollection;

class PersonaleValidator
{
    public static function validate(Personale $personale,  ExecutionContextInterface $context, $payload)
    {
        if ($personale->getProvincia()->getCode() === 'XX') {

            $context->buildViolation('Provincia obbligatoria')
                ->addViolation()
            ;
        }
        $NazComNascita = ''; $LetComNascita = ''; $ExtraCEE = false ;
        $NazCee = ['Z102', 'Z103', 'Z104', 'Z107', 'Z109', 'Z110', 'Z112', 'Z115', 'Z116', 'Z120',
                'Z121', 'Z126', 'Z127', 'Z128', 'Z129', 'Z131', 'Z132', 'Z134', 'Z144', 'Z145', 'Z146',
                'Z149', 'Z150', 'Z155', 'Z156', 'Z211'];
        $codicefiscale = new CodiceFiscaleValidation;
        $codiceFiscaleVerify = $codicefiscale->verifyFiscalCode($personale->getFiscalCode());
        //print_r($codiceFiscaleVerify);
        if ($codiceFiscaleVerify['Retcode'] === 'ER') {
            $context->buildViolation($codiceFiscaleVerify['Message'])
                ->addViolation() ; } 
        else {
                // Codice valido 
                $NazComNascita = \substr($personale->getFiscalCode(), 12, 4);
                $LetComNascita = \substr($NazComNascita, 1, 1);
                if ($LetComNascita === "Z" ) { 
                    if (!in_array($NazComNascita, $NazCee)) {
                        $ExtraCEE = true ;
                    }
                }
                // controlla corrispondenza sesso
                if ($personale->getGender() != $codiceFiscaleVerify['Gender']) {
                 $context->buildViolation('Il sesso non corrisponde al codice fiscale')
                 ->addViolation() ; }
                 else { 
                     // sesso corrisponde, se data di nascita non impostata, viene assegnata
                     if ($personale->getBirthday() === null ) { $personale->setBirthday($codiceFiscaleVerify['Birthday']) ;} 
                     else { 
                         if ($personale->getBirthday() != $codiceFiscaleVerify['Birthday'] ) {
                            $context->buildViolation('La data di nascita non corrisponde al codice fiscale')
                            ->addViolation() ;
                         }
                     }  
                }    
             }
        // Controlla piano ore settimanali
        $hourdayarray = $personale->getPlanHourWeek();
        $totday = 0;
        $title = 'PIANO ORE SETTIMANALI:';
        foreach ($hourdayarray as $d) {
            $totday++ ;
            if (is_numeric($d)) {
                if ($d < 0 || $d > 8) {  $context->buildViolation(sprintf('%s le ore %s impostate al %d^item sono fuori dai limiti consentiti', $title,  $d, $totday))
                                         ->addViolation() ; }
            } else 
            {  $context->buildViolation(sprintf('%s al %d^item inserire un numero compreso tra 0 e 8',$title, $totday))
                ->addViolation() ; }
        }
        if ($totday != 7 ) {
            $context->buildViolation(sprintf('%s inserire 7 item rispettivamente dal lunedì alla domenica',$title))
            ->addViolation() ;
        }
        
        // Controlla data scadenza contratto se tipo contratto D / T
        if ($personale->getTipoContratto() === 'D' || $personale->getTipoContratto() === 'T'  ) {
            if ($personale->getScadenzaContratto() === null) {
                $context->buildViolation('Per tipo contratti a tempo Determinato o Stagionali occorre indicare la data di scadenza contratto di lavoro')
                ->addViolation() ;
            }
        }

        // Controlla presenza mansione se invalido
        if ($personale->getIsInvalid() === true &&  $personale->getMansione() === null ) {
            $context->buildViolation('Per le persone diversamente abili è obbligatorio indicare la mansione lavorativa.')
            ->addViolation() ;
        } 

        // Controlla data scadenza visita medica
        if ($personale->getUltimaVisitaMedica() !== null && $personale->getScadenzaVisitaMedica() === null ){
            $context->buildViolation('Se già eseguita la visita medica, occorre indicare anche la scadenza della prossima visita.')
            ->addViolation() ;
        } 

        // Controlla documenti 
        $documentiPersonale = new ArrayCollection;
        $documentiPersonale = $personale->getDocumentiPersonale();
        foreach ($documentiPersonale as $documento) {
            if ($documento->getDocumentoFile() !== null && $documento->getTitolo() === null && ($documento->getTipologia() === 'NUL' || $documento->getTipologia() === 'OTH') ) {
                $context->buildViolation('Se scegli un file documento generico allora devi inserire anche un Titolo (oppure seleziona un tipo documento appropriato)')
                    ->addViolation() ;
            }
            if ($documento->getDocumentoFile() === null && $documento->getDocumentoPath() === null && ( $documento->getTitolo() !== null || $documento->getTipologia() !== null  ) ) {
                $context->buildViolation('Se inserisci un Tipo o un Titolo documento allora devi scegliere un file (pdf o immagini, dalle dimensioni massime di 3MB)')
                ->addViolation() ;
            }

        }

        // Persona convalidata aumenta il livello dei controlli
        if ($personale->getIsValidated() === true) {
            if ( ($personale->getMobile() === null || $personale->getMobile() === '' ) &&
                ($personale->getPhone() === null || $personale->getPhone() === '') &&
                ($personale->getEmail() === null || $personale->getEmail() === '')
            )  {
                $context->buildViolation('Almeno un dato tra numero di cellulare, telefono, e-mail è obbligatorio, se la Persona è Convalidata')
                ->addViolation() ;
            }
            // verifica documenti, anche in funzione del tipo documento e della nazionalità
            $oggi = new \DateTime('now');
            $scheda = false;
            $permessoSoggiorno = false;
            $documentoIdentita = false;
            $documentoInvalidi = false;
            $documentiPersonale = $personale->getDocumentiPersonale();
            foreach ($documentiPersonale as $documento) {
                $tipo = $documento->getTipologia();
                switch ($tipo) {
                    case "SAP":
                        $scheda = true;
                        break;
                    case "INP":
                    case "INF":
                        $documentoInvalidi = true;
                        if ($documento->getScadenza() === null || $documento->getScadenza() <=  $oggi) {
                            $context->buildViolation('Per il documento di invalidità indicare una data di scadenza valida')
                            ->addViolation() ;
                        }
                        break;
                    case "PSG":
                        $permessoSoggiorno = true;
                        if ($documento->getScadenza() === null || $documento->getScadenza() <=  $oggi) {
                            $context->buildViolation('Per il Permesso di Soggiorno indicare una data di scadenza valida')
                            ->addViolation() ;
                        }
                        break;
                    case "CID":
                        $documentoIdentita = true;
                        if ($documento->getScadenza() === null || $documento->getScadenza() <=  $oggi) {
                            $context->buildViolation('Per il Documento di Identità indicare una data di scadenza valida')
                            ->addViolation() ;
                        }
                        break;
                    case "PAT":
                        $documentoIdentita = true;
                        if ($documento->getScadenza() === null || $documento->getScadenza() <=  $oggi) {
                            $context->buildViolation('Per la Patente indicare una data di scadenza valida')
                            ->addViolation() ;
                        }
                        break; 
                    case "PAS":
                        $documentoIdentita = true;
                        if ($documento->getScadenza() === null || $documento->getScadenza() <=  $oggi) {
                            $context->buildViolation('Per il Passaporto indicare una data di scadenza valida')
                            ->addViolation() ;
                        }
                        break;             
                    }
            }
            if ($scheda === false) {
                $context->buildViolation('Occorre caricare il documento Scheda Anagrafica Personale')
                ->addViolation() ;
            }
            if ($personale->getIsInvalid() === true &&  $documentoInvalidi === false ) {
                $context->buildViolation('Per le persone diversamente abili è obbligatorio caricare un documento di invalidità')
                ->addViolation() ;
            } 
            if ($personale->getIsInvalid() === false &&  $documentoInvalidi === true ) {
                $context->buildViolation('Caricato un documento di invalidità, ma la persona non è stata selezionata come diversamente abile')
                ->addViolation() ;
            } 
            if ($ExtraCEE === true ) {
                if ($permessoSoggiorno === false) {
                    $context->buildViolation('Per le persone Extra comunitarie è obbligatorio caricare il Permesso di Soggiorno')
                    ->addViolation() ;
                }
            } 
            if ($documentoIdentita === false) {
                $context->buildViolation('Non è stato caricato nessun documento di riconoscimento, intentità, passaporto o patente.')
                ->addViolation() ;
            }
        }
    }
}