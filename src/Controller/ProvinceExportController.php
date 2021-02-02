<?php

namespace App\Controller;

use App\Entity\Province;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Routing\Annotation\Route;

class ProvinceExportController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct( EntityManagerInterface $entityManager)
    {

        $this->entityManager = $entityManager;
    }

    /**
     * @Route("uploads/files/excel/export/province.xlsx", name="/admin/excel")
    */
    public function excel()
    {
        return $this->render('excel/index.html.twig', [
            'controller_name' => 'Export Province',
        ]);
    }
    

    private function getData(): array
    {
        /**
         * @var $province Province[]
         */
        $list = [];
        $provincelist = $this->entityManager->getRepository(Province::class)->findAll();

        foreach ($provincelist as $province) {
            $list[] = [
                $province->getCode(),
                $province->getName(),
                $province->getCreatedAt()
            ];
        }
        return $list;
    }

    /**
     * @Route("/admin/export",  name="export")
     */
    public function export()
    {
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Lista Province');

        $sheet->getCell('A1')->setValue('Sigla');
        $sheet->getCell('B1')->setValue('Nome');
        $sheet->getCell('C1')->setValue('Aggiornata il');

        // Increase row cursor after header write
            $sheet->fromArray($this->getData(),null, 'A2', true);
        

        $writer = new Xlsx($spreadsheet);

        $writer->save('uploads/files/excel/export/province.xlsx');
        return $this->redirectToRoute('/admin/excel'); 
    }
}