<?php
/**
 * @copyright Copyright © 2023 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\PDF\FPDF;

use tFPDF;

class FPDF extends tFPDF
{
    // Fix tfpdf to do UTF8 encoding when $isUTF8 is true
    function SetAuthor($author, $isUTF8 = false)
    {
        $this->metadata['Author'] = $isUTF8 ? $this->_UTF8encode($author) : $author;
    }

    function SetCreator($creator, $isUTF8 = false)
    {
        $this->metadata['Creator'] = $isUTF8 ? $this->_UTF8encode($creator) : $creator;
    }

    function SetKeywords($keywords, $isUTF8 = false)
    {
        $this->metadata['Keywords'] = $isUTF8 ? $this->_UTF8encode($keywords) : $keywords;
    }

    function SetSubject($subject, $isUTF8 = false)
    {
        $this->metadata['Subject'] = $isUTF8 ? $this->_UTF8encode($subject) : $subject;
    }

    function SetTitle($title, $isUTF8 = false)
    {
        $this->metadata['Title'] = $isUTF8 ? $this->_UTF8encode($title) : $title;
    }
    // End fix tfpdf

    public function getMetadata(string $key): array|string
    {
        return $key === '' ? $this->metadata : $this->metadata[$key];
    }
}
