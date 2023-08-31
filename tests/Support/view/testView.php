<?php
/**
 * @copyright Copyright © 2023 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

use BeastBytes\PDF\FPDF\Document;
use BeastBytes\PDF\FPDF\FPDF;

/** @var \BeastBytes\PDF\FPDF\Document $document */

$document->AddFont('DejaVu', Document::FONT_STYLE_REGULAR, 'DejaVuSansCondensed.ttf', Document::UTF8);
$document->AddPage();

$document->SetFont('DejaVu', '', 10);
$document->Write(5, 'Test Text');
