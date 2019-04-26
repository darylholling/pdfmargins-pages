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
        $fpdi = new Fpdi();

        $x = 'input';
        $y = 'input' ?: $x;

        $labels = $this->get('kernel')->getProjectDir() . '/assets/images/labelsmetrand.pdf';
        $pageCount = $fpdi->setSourceFile($labels);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $fpdi->AddPage('P', [self::WIDTH, self::HEIGHT]);

            if (isset($x)) {
                $fpdi->useTemplate($fpdi->importPage($pageNo), $x, $y, self::WIDTH - (10 * 2), self::HEIGHT - (10 * 2));
            } else {
                $fpdi->useTemplate($fpdi->importPage($pageNo), 0, 0, self::WIDTH, self::HEIGHT);

            }

        }

        $fpdi->Output();

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
        $fpdi = new Fpdi();

        $labels = $this->get('kernel')->getProjectDir() . '/assets/images/labelsmetrand.pdf';

        $pageCount = $fpdi->setSourceFile($labels);
        $sourcePages = [];

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $sourcePages[] = $fpdi->importPage($pageNo);
        }

        $itemCountForPageOne = 5 - $position;

        $itemsForPageOne = array_slice($sourcePages, 0, $itemCountForPageOne);

        array_splice($sourcePages, 0, $itemCountForPageOne, []);

        $pages = array_chunk($sourcePages, 4);

        $start = $position;

        $this->renderPage($fpdi, $start, $itemsForPageOne);

        foreach ($pages as $page) {
            $start = 1;

            $this->renderPage($fpdi, $start, $page);
        }

        $fpdi->Output();
    }

    /**
     * @param $fpdi
     * @param $start
     * @param $labels
     */
    private function renderPage($fpdi, &$start, $labels): void
    {
        $fpdi->AddPage();

        foreach ($labels as $label) {
            $odd = $start % 2 !== 0;

            $x = ($odd ? 0 : 1) * self::WIDTH;
            $y = ($start >= 3 ? 1 : 0) * self::HEIGHT;

            $fpdi->useTemplate($label, $x, $y, self::WIDTH, self::HEIGHT);

            $start++;
        }
    }
}