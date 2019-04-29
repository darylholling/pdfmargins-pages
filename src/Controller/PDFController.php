<?php


namespace App\Controller;

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\PdfParser\Filter\FilterException;
use setasign\Fpdi\PdfParser\PdfParserException;
use setasign\Fpdi\PdfParser\Type\PdfTypeException;
use setasign\Fpdi\PdfReader\PdfReaderException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PDFController
 * @package App\Controller
 * @Route("/pdf")
 */
class PDFController extends Controller
{
    private const WIDTH = 105;
    private const HEIGHT = 148.4;

    /**
     * @Route("/")
     * @throws PdfParserException
     * @throws PdfReaderException
     */
    public function pdf(): void
    {
        $this->loop(2);
    }

    /**
     * @Route("/margins")
     * @throws PdfParserException
     * @throws PdfReaderException
     * @throws CrossReferenceException
     * @throws FilterException
     * @throws PdfTypeException
     */
    public function snipMargins(): void
    {
        $pdf = new Fpdi();

        $x = 'inputx';
        $y = 'inputy' ?: $x;

        $labels = $this->get('kernel')->getProjectDir() . '/assets/images/labelsmetrand.pdf';
        $pageCount = $pdf->setSourceFile($labels);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $pdf->AddPage('P', [self::WIDTH, self::HEIGHT]);

            if (isset($x)) {
//                $pdf->useTemplate($pdf->importPage($pageNo), $x, $y, self::WIDTH - ($x * 2), self::HEIGHT - ($y * 2));
                $pdf->useTemplate($pdf->importPage($pageNo), 10, 10, self::WIDTH - (10 * 2), self::HEIGHT - (10 * 2));
            } else {
                $pdf->useTemplate($pdf->importPage($pageNo), 0, 0, self::WIDTH, self::HEIGHT);

            }

        }

        $pdf->Output();
    }


    /**
     * @param int $position
     * @throws PdfParserException
     * @throws PdfReaderException
     * @throws CrossReferenceException
     * @throws FilterException
     * @throws PdfTypeException
     */
    private function loop(int $position): void
    {
        $pdf = new Fpdi();

        $labels = $this->get('kernel')->getProjectDir() . '/assets/images/labelsmetrand.pdf';

        $pageCount = $pdf->setSourceFile($labels);
        $sourcePages = [];

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $sourcePages[] = $pdf->importPage($pageNo);
        }

        $itemCountForPageOne = 5 - $position;

        $itemsForPageOne = array_slice($sourcePages, 0, $itemCountForPageOne);

        array_splice($sourcePages, 0, $itemCountForPageOne, []);

        $pages = array_chunk($sourcePages, 4);

        $start = $position;

        $this->renderPage($pdf, $start, $itemsForPageOne);

        foreach ($pages as $page) {
            $start = 1;

            $this->renderPage($pdf, $start, $page);
        }

        $pdf->Output();
    }

    /**
     * @param $pdf
     * @param $start
     * @param $labels
     */
    private function renderPage($pdf, &$start, $labels): void
    {
        $pdf->AddPage();

        foreach ($labels as $label) {
            $odd = $start % 2 !== 0;

            $x = ($odd ? 0 : 1) * self::WIDTH;
            $y = ($start >= 3 ? 1 : 0) * self::HEIGHT;

            $pdf->useTemplate($label, $x, $y, self::WIDTH, self::HEIGHT);

            $start++;
        }
    }
}