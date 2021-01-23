<?php
namespace App\Validator\Routine;
use Symfony\Component\Validator\Constraints\DateTime;

class CodiceFiscaleValidation 
{
    public function verifyFiscalCode(string $fiscalcode):array
    {
        // Valori iniziali 
        $retcode = 'ER';
        $message = 'Codice fiscale non valido';
        $gender = 'N';
        $birthday =  new \DateTime('1900-01-01');
             
        $dispari = ["0" => 1, "1" => 0, "2" => 5 , "3" => 7, "4" => 9,  "5" => 13 , "6" => 15, "7" => 17, "8" => 19, "9" => 21,
         "A" => 1 , "B" => 0, "C" => 5 , "D" => 7, "E" => 9,  "F" => 13 , "G" => 15, "H" => 17, "I" => 19, "J" => 21, 
         "K" => 2, "L" => 4, "M" => 18, "N" => 20, "O" => 11, "P" => 3, "Q" => 6, "R" => 8, "S" => 12, "T" => 14, "U" => 16,
         "V" => 10, "W" => 22, "X" => 25, "Y" => 24, "Z" => 23 ];

        $pari = ["0" => 0, "1" => 1, "2" => 2 , "3" => 3, "4" => 4,  "5" => 5 , "6" => 6, "7" => 7, "8" => 8, "9" => 9,
         "A" => 0 , "B" => 1, "C" => 2 , "D" => 3, "E" => 4,  "F" => 5 , "G" => 6, "H" => 7, "I" => 8, "J" => 9, 
         "K" => 10, "L" => 11, "M" => 12, "N" => 13, "O" => 14, "P" => 15, "Q" => 16, "R" => 17, "S" => 18, "T" => 19, "U" => 20,
         "V" => 21, "W" => 22, "X" => 23, "Y" => 24, "Z" => 25 ];

        $check = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"];

        $month = ["A" => 1 , "B" => 2, "C" => 3 , "D" => 4, "E" => 5, "H" => 6, "L" => 7, "M" => 8, "P" => 9, "R" => 10, "S" => 11, "T" => 12 ];

        // logica di controllo
        if (strlen($fiscalcode) === 16) { 

            $cfarr = str_split($fiscalcode);
            $checkcontrolchar = $cfarr[15];
            $paribool = false;
            $totdigit = 0;

            for ($i = 0; $i < 15; $i++) {
                if ($paribool === false) { 
                    // tratta i digit dispari 1,3,5,...
                    $totdigit += $dispari[$cfarr[$i]];
                }
                else {
                    // tratta i digit pari 2,4,6,...
                    $totdigit += $pari[$cfarr[$i]];
                 }
                // cambia flag
                $paribool = ($paribool === false ) ? true : false ;
            } 
          // Calcola il resto
          $resto = ( $totdigit % 26 ) ;  
          // Verifica con checkcontrolchar
          if ($check[$resto] === $checkcontrolchar) { 
            $retcode = 'OK';
            $message = 'Codice fiscale valido';

          // Calcola sesso 
            $gg = ($cfarr[9]*10)+$cfarr[10];
            if ($gg > 40) { $gender = "F" ;
                $gg -= 40 ; }
            else { $gender = "M" ;}    
          // Calcola data di nascita
            $aa = ($cfarr[6]*10)+$cfarr[7];
            if ($aa > date('y') ) { $aa += 1900 ;}
            else { $aa += 2000  ;} 
            $birthday = new \DateTime($aa.'-'. $month[$cfarr[8]].'-'.$gg);
            } 

        } else { $message = 'Il codice fiscale deve contenere 16 caratteri'; }
        
        return ['Retcode' => $retcode , 'Message' => $message, 'Gender' => $gender, 'Birthday'=> $birthday];
    }
}
