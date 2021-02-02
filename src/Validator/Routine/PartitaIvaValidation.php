<?php
namespace App\Validator\Routine;

class PartitaIvaValidation 
{
    public function verifyPartitaIva(string $partitaiva=null):array
    {
        // Valori iniziali 
        $retcode = 'OK';
     
       
        // per adesso logica di controllo limitata ai numeri, da aggiungere algoritmo check digit
        if (strlen($partitaiva) === 11) { 
            $piarray = str_split($partitaiva);
            foreach ($piarray as $d) {
                if (!is_numeric($d)) {
                    $retcode = 'ER';
                    $message = 'Partita iva non valida';
                    break;
                }
            }
        // 
          if ($retcode === 'OK') { 
            $message = 'Partita iva valida';
            } 

        } else {  $retcode = 'ER'; $message = 'La partita iva deve contenere 11 caratteri numerici'; }
        
        return ['Retcode' => $retcode , 'Message' => $message];
    }
}
