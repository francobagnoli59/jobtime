<?php

namespace App\ServicesRoutine;


class PhpOfficeStyle
{
    // Ritorma l'array codificato secondo lo stile impostato 
     
    public function styleSetting($style): array
    {
      switch ($style) {
        case "title1":
            return $this->title1();
            break;
        case "title2":
          return $this->title2();
          break;
        case "title3":
          return $this->title3();
          break;
        case "corsivo3":
          return $this->corsivo3();
          break;
        case "columnTitleGrey":
          return $this->columnTitleGrey();
          break;
        case "columnTitleCoral":
          return $this->columnTitleCoral();
          break;
        case "rowGrey":
          return $this->rowGrey();
          break;
        case "rowCoral":
          return $this->rowCoral();
          break;
        case "columnTotal":
          return $this->columnTotal();
          break;
        case "rowTotal":
          return $this->rowTotal();
          break;
        case "backGroundRed":
          return $this->backGroundRed();
          break;
        case "backGroundYellow":
          return $this->backGroundYellow();
          break;
        case "backGroundLime":
          return $this->backGroundLime();
          break;
        case "backGroundAqua":
          return $this->backGroundAqua();
          break;
        case "backGroundSilver":
          return $this->backGroundSilver();
          break;
        case "backGroundFuchsia":
          return $this->backGroundFuchsia();
          break;
        case "alignHLeft":
          return $this->alignHLeft();
          break;
        case "alignHCenter":
          return $this->alignHCenter();
          break;
        case "alignHRight":
          return $this->alignHRight();
          break;

        default :
            $styleArray = [
              'font' => [
                  'size' => 11,
              ],
              'alignment' => [
                  'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
              ],
            ];
            return $styleArray;
            break;
          }
    }


    public function title1(): array
    {
     // Calibri 16 Grassetto nero,sfondo bianco, senza bordi, allineato a sinistra
      $styleArray = [
        'font' => [
            'bold' => true,
            'size' => 16,
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
        ],
      ];
      return $styleArray;
    }

    public function title2(): array
    {
     // Calibri 14 Grassetto nero,sfondo bianco, senza bordi, allineato a sinistra
      $styleArray = [
        'font' => [
            'bold' => true,
            'size' => 14,
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
        ],
      ];
      return $styleArray;
    }

    public function title3(): array
    {
     // Calibri 12 Grassetto nero,sfondo bianco, senza bordi, allineato a sinistra
      $styleArray = [
        'font' => [
            'bold' => true,
            'size' => 12,
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
        ],
      ];
      return $styleArray;
    }

    public function corsivo3(): array
    {
     // Calibri 12 Grassetto nero, italic,sfondo bianco, senza bordi, allineato al centro
      $styleArray = [
        'font' => [
            'bold' => true,
            'italic' => true,
            'size' => 12,
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        ],
      ];
      return $styleArray;
    }

    public function columnTitleGrey(): array
    {
     // Calibri 12 Grassetto nero, sfondo sfumato grigio, senza bordi, allineato al centro
      $styleArray = [
        'font' => [
            'bold' => true,
            'size' => 12,
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
            'rotation' => 90,
            'startColor' => [
                'argb' => 'FFA0A0A0',
            ],
            'endColor' => [
                'argb' => 'FFE6E6E6',
            ],
        ],
      ];
      return $styleArray;
    }

    public function columnTitleCoral(): array
    {
     // Calibri 12 Grassetto nero, sfondo sfumato corallo, senza bordi, allineato al centro
      $styleArray = [
        'font' => [
            'bold' => true,
            'size' => 12,
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
            'rotation' => 90,
            'startColor' => [
                'argb' => 'FFFF7F50',
            ],
            'endColor' => [
                'argb' => 'FFFFE6DC',
            ],
        ],
      ];
      return $styleArray;
    }

    public function rowGrey(): array
    {
     // Calibri 11 nero, sfondo sfumato grigio, senza bordi, allineato al centro
      $styleArray = [
        'font' => [
            'size' => 11,
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => [
                'argb' => 'FFE6E6E6',
            ],
          ],
      ];
      return $styleArray;
    }
    
    public function rowCoral(): array
    {
      // Calibri 11 nero, sfondo sfumato corallo, senza bordi, allineato al centro
      $styleArray = [
        'font' => [
            'size' => 11,
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => [
                'argb' => 'FFFFE6DC',
            ],
          ],
      ];
      return $styleArray;
    }

    public function columnTotal(): array
    {
     // Calibri 12 Grassetto nero, sfondo sfumato skyblue, bordo al top, allineato al centro
      $styleArray = [
        'font' => [
            'bold' => true,
            'size' => 12,
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        ],
        'borders' => [
            'top' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
            'rotation' => 90,
            'startColor' => [
                'argb' => 'FF87CEFA',
            ],
            'endColor' => [
                'argb' => 'FFE1F3FE',
            ],
        ],
      ];
      return $styleArray;
    }

    public function rowTotal(): array
    {
     // Calibri 12 Grassetto nero, sfondo sfumato skyblue, nessun bordo, allineato al centro
      $styleArray = [
        'font' => [
            'bold' => true,
            'size' => 12,
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => [
              'argb' => 'FFE1F3FE',
            ],
        ],
      ];
      return $styleArray;
    }

    public function backGroundRed(): array
    {
     // sfondo red, nessun bordo
      $styleArray = [
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => [
                'argb' => 'FFFF0000',
            ],
          ],
      ];
      return $styleArray;
    }

    public function backGroundYellow(): array
    {
      // sfondo yellow, nessun bordo
      $styleArray = [
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => [
                'argb' => 'FFFFFF00',
            ],
          ],
      ];
      return $styleArray;
    }

    public function backGroundLime(): array
    {
     // sfondo limw, nessun bordo
      $styleArray = [
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => [
                'argb' => 'FF00FF00',
            ],
          ],
      ];
      return $styleArray;
    }

    public function backGroundAqua(): array
    {
       // sfondo aqua, nessun bordo
      $styleArray = [
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => [
                'argb' => 'FF00FFFF',
            ],
          ],
      ];
      return $styleArray;
    }

    public function backGroundSilver(): array
    {
     // sfondo silver, nessun bordo
      $styleArray = [
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => [
                'argb' => 'FFC0C0C0',
            ],
          ],
      ];
      return $styleArray;
    }

    public function backGroundFuchsia(): array
    {
     // sfondo  fuchsia, nessun bordo
      $styleArray = [
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => [
                'argb' => 'FFFF00FF',
            ],
          ],
      ];
      return $styleArray;
    }

    public function alignHLeft(): array
    {
     //
      $styleArray = [
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
        ],
      ];
      return $styleArray;
    }

    public function alignHCenter(): array
    {
     // Calibri 16 Grassetto nero,sfondo bianco, senza bordi, allineato a sinistra
      $styleArray = [
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        ],
      ];
      return $styleArray;
    }

    public function alignHRight(): array
    {
     // Calibri 16 Grassetto nero,sfondo bianco, senza bordi, allineato a sinistra
      $styleArray = [
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
        ],
      ];
      return $styleArray;
    }
}
