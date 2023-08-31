<?php
/**
 * @copyright Copyright © 2023 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\PDF\FPDF\Tests;

use BeastBytes\PDF\FPDF\Document;
use BeastBytes\PDF\FPDF\FPDF;
use BeastBytes\PDF\FPDF\Tests\Support\TestCase;
use BeastBytes\PDF\PdfInterface;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

use const DIRECTORY_SEPARATOR;

class PdfTest extends TestCase
{
    private const INVALID_DESTINATION = 'X';

    public function testGeneratePdfOutputString(): void
    {
        $pdf = $this->get(PdfInterface::class);

        $document = $pdf
            ->generate('testView')
            ->withSubject('Test String')
        ;

        $output = $pdf->output($document, Document::DESTINATION_STRING);

        $this->assertIsString($output);
        $this->assertStringStartsWith('%PDF', $output);
        $this->assertStringContainsString('/Producer (tFPDF', $output);
        $this->assertStringContainsString('/Creator (BeastBytes\\\\PDF\\\\FPDF\\\\FPDF)', $output);
        $this->assertStringContainsString('/Subject (Test String)', $output);
    }

    public function testGeneratePdfOutputFile(): void
    {
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'Support' . DIRECTORY_SEPARATOR . 'pdf';
        $name = 'test.pdf';

        $pdf = $this->get(PdfInterface::class);

        $document = $pdf
            ->generate('testView')
            ->withSubject('Test')
            ->withName($name)
            ->withPath($path)
        ;

        $this->assertTrue($pdf->output($document, Document::DESTINATION_FILE));

        $this->assertFileExists($path . DIRECTORY_SEPARATOR . $name);
    }

    public function testOutputDownload(): void
    {
        $pdf = $this->get(PdfInterface::class);

        $document = $pdf
            ->generate('testView')
            ->withSubject('Test String')
            ->withName('test.pdf')
        ;

        $this->assertInstanceOf(
            ResponseInterface::class,
            $pdf->output($document, Document::DESTINATION_DOWNLOAD)
        );

        $document = $pdf
            ->generate('testView')
            ->withSubject('Test String')
        ;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(Document::NAME_NOT_SET_EXCEPTION);
        $pdf->output($document, Document::DESTINATION_INLINE);
    }

    public function testOutputInline(): void
    {
        $pdf = $this->get(PdfInterface::class);

        $document = $pdf
            ->generate('testView')
            ->withSubject('Test String')
            ->withName('test.pdf')
        ;

        $this->assertInstanceOf(
            ResponseInterface::class,
            $pdf->output($document, Document::DESTINATION_INLINE)
        );

        $document = $pdf
            ->generate('testView')
            ->withSubject('Test String')
        ;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(Document::NAME_NOT_SET_EXCEPTION);
        $pdf->output($document, Document::DESTINATION_INLINE);
    }

    public function testInvalidDestination(): void
    {
        $pdf = $this->get(PdfInterface::class);

        $document = $pdf
            ->generate('testView')
            ->withSubject('Test String')
        ;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(Document::INVALID_OUTPUT_DESTINATION_EXCEPTION);
        $pdf->output($document, self::INVALID_DESTINATION);
    }
}
