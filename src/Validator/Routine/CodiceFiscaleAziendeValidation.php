<?php
namespace App\Validator\Routine;

class CodiceFiscaleAziendeValidation 
{
    public function verifyAziendeFiscalCode(string $fiscalcode):array
    {
        // Valori iniziali 
        $retcode = 'OK';
     
       
        // per adesso logica di controllo limitata ai numeri, da aggiungere algoritmo check digit
        if (strlen($fiscalcode) === 11) { 
            $cfarray = str_split($fiscalcode);
            foreach ($cfarray as $d) {
                if (!is_numeric($d)) {
                    $retcode = 'ER';
                    $message = 'Codice fiscale non valido';
                    break;
                }
            }
        // 
          if ($retcode === 'OK') { 
            $message = 'Codice fiscale valido';
            } 

        } else {  $retcode = 'ER'; $message = 'Il codice fiscale deve contenere 11 caratteri numerici'; }
        
        return ['Retcode' => $retcode , 'Message' => $message];
    }
}
