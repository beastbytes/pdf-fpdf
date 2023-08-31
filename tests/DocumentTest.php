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

class DocumentTest extends TestCase
{
    private Document $document;

    protected function setUp(): void
    {
        parent::setUp();
        $this->document = new Document();
    }

    public function testAuthor(): void
    {
        $author = 'Test author';
        $document = $this->document->withAuthor($author);

        $this->assertNotSame($document, $this->document);
        $this->assertSame($author, $document->getAuthor());
    }

    public function testDefaultCreator(): void
    {
        $this->assertSame(FPDF::class, $this->document->getCreator());
    }

    public function testCreator(): void
    {
        $creator = 'Test creator';
        $document = $this->document->withCreator($creator);

        $this->assertNotSame($document, $this->document);
        $this->assertSame($creator, $document->getCreator());
    }

    /*
    public function testCustomProperties(): void
    {
        $customProperties = [
            'Custom property 1' => 'Value 1',
            'Custom property 2' => 'Value 2',
            'Custom property 3' => 'Value 3',
            'Custom property 4' => 'Value 4',
        ];
        $document = $this->document->withCustomProperties($customProperties);

        $this->assertNotSame($document, $this->document);
        $this->assertSame($customProperties, $document->getCustomProperties());
    }
    */

    public function testKeywords(): void
    {
        $keywords = 'test keywords';
        $document = $this->document->withKeywords($keywords);

        $this->assertNotSame($document, $this->document);
        $this->assertSame($keywords, $document->getKeywords());
    }

    public function testName(): void
    {
        $name = 'Test name';
        $document = $this->document->withName($name);

        $this->assertNotSame($document, $this->document);
        $this->assertSame($name, $document->getName());
    }

    public function testSubject(): void
    {
        $subject = 'Test subject';
        $document = $this->document->withSubject($subject);

        $this->assertNotSame($document, $this->document);
        $this->assertSame($subject, $document->getSubject());
    }

    public function testTitle(): void
    {
        $title = 'Test title';
        $document = $this->document->withTitle($title);

        $this->assertNotSame($document, $this->document);
        $this->assertSame($title, $document->getTitle());
    }

    public function testUtf8(): void
    {
        $document = $this->document->withUTF8(true);

        $this->assertNotSame($document, $this->document);
        $this->assertFalse($this->document->isUTF8());
        $this->assertTrue($document->isUTF8());
    }
}
