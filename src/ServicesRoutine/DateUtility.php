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
}
