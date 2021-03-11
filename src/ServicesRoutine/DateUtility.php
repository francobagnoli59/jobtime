<?php

namespace App\ServicesRoutine;


class DateUtility
{
    // year = yyyy , month = mm , return array [0=ultimo giorno del mese (int dd) ,
    //                             1=primo giorno del mese (DateTime yyyymmdd),  2=ultimo giorno del mese (DateTime yyyymmdd)]
    
    public function calculateLimitMonth($year, $month): array
    {
      // calcola limiti del mese, controlla anni bisestili fino al 2048  
      $limitMonth = [];  
      // valori iniziali per preparazione mese
      $lastDayOfMonth = 31;
      if ($month === '04' || $month === '06' || $month === '09' || $month === '11') {
        $lastDayOfMonth = 30;
      }
      if ($month === '02' ) {
          $lastDayOfMonth = 28;
          if ($year === '2024' || $year === '2028' || $year === '2032' || $year === '2036' || $year === '2040' || $year === '2044' || $year === '2048') {
              $lastDayOfMonth = 29;
          }
      }
      $limitMonth[0] = $lastDayOfMonth;  // dd ultimo giorno del mese
      $datestart = new \DateTime();
      $datestart->setTime(0,0,0);
      $dateend = new \DateTime();
      $dateend->setTime(0,0,0);
      $datestart->setDate($year, $month, 1);  
      $dateend->setDate($year, $month, $lastDayOfMonth);
      $limitMonth[1] = $datestart;  // Date Object yyyymmdd primo giorno del mese
      $limitMonth[2] = $dateend;    // Date Onject yyyymmdd ultimo giorno del mese
      return  $limitMonth ;
    }

    // fornito un anno ($year) e un mese ($month) calcola il primo e l'ultimo giorno del mese 
    // partendo da $year e $month e andando indietro nel tempo di $monthdiff (se negativo) o avanti se positivo
    // comunque ritorna un array relativo a 12 mesi.
    // esempio: 
    // se la funzione riceve  anno=2020 e mese=6 ( quindi giugno) e back=-9 l'array di ritorno
    // nelle prime 12 key (da elemento 0 a 11) avrò DateObject che conterranno
    // 20191001, 20191101, 20191201, 20200101, 20200201, 20200301, 20200401, 20200501, 20200601, 20200701, 20200801, 20200901
    // quindi nelle successive 12 key (da elemento 12 a 23) ci sarnno i fine mese e cioè:
    // 20191031, 20191130, 20191231, 20200131, 20200229, 20200331, 20200430, 20200531, 20200630, 20200731, 20200831, 20200930.
    // ovviamente la funzione tiene conto degli anni bisestili.
    public function calculateRangeYear($year, $month, $monthdiff): array
    {
      $rangeMonth = []; 
      $mm = $month + $monthdiff;
      if ($mm < 0) { 
        $yy = intdiv($mm, 12) ;  $year += $yy ;
        $rm = ($mm % 12 ); 
        if ($rm < 0) {$year -= 1 ; $meseStart = $rm + 13; } else { $meseStart =  1; }
       } else { 
        $yy = intdiv($mm, 12) ;  $year += $yy ;
        $rm = ($mm % 12 ); 
        if ($rm > 0) { $meseStart = $rm ; } else { $meseStart = 1; } 
       }
       $mese = $meseStart;
      for ($i=1 ; $i<=12; $i++) {
        $retArr = $this->calculateLimitMonth($year, $mese) ;
        $rangeMonth[$i-1] = $retArr[1];
        $rangeMonth[$i+11] = $retArr[2];
        $mese++;
        if ($mese === 13) { $mese = 1; $year++;}
      }
      return $rangeMonth;
    }

}
