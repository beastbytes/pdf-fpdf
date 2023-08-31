<?php
/**
 * @copyright Copyright © 2023 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\PDF\FPDF;

use BeastBytes\PDF\Document as BaseDocument;
use BeastBytes\PDF\DocumentInterface;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Yiisoft\Http\ContentDispositionHeader;
use Yiisoft\ResponseDownload\DownloadResponseFactory;

use const DIRECTORY_SEPARATOR;

final class Document extends BaseDocument
{
    public const AUTO_PAGE_BREAK = true;
    // Output destinations
    public const DESTINATION_DOWNLOAD = 'D';
    public const DESTINATION_FILE = 'F';
    public const DESTINATION_INLINE = 'I';
    public const DESTINATION_STRING = 'S';
    // --end--
    public const DIRECTORY_NOT_CREATED_EXCEPTION = 'Directory `%s` was not created';
    public const INVALID_OUTPUT_DESTINATION_EXCEPTION = 'Invalid output destination';
    public const NAME_NOT_SET_EXCEPTION = 'Filename not set';
    // Borders - used by Cell() and MultiCell()
    public const BORDER_NONE = 0;
    public const BORDER_ALL = 1;
    public const BORDER_FRAME = 1;
    public const BORDER_BOTTOM = 'B';
    public const BORDER_LEFT = 'L';
    public const BORDER_RIGHT = 'R';
    public const BORDER_TOP = 'T';
    // --end--
    public const COMPRESSION = true;
    public const FILL_CELL = true;
    // Font styles - used by SetFont()
    public const FONT_STYLE_BOLD = 'B';
    public const FONT_STYLE_ITALIC = 'I';
    public const FONT_STYLE_REGULAR = '';
    public const FONT_STYLE_UNDERLINE = 'U';
    // --end--
    // Current position after printing a cell - used by Cell()
    public const GOTO_RIGHT = 0;
    public const GOTO_NEXT_LINE = 1;
    /** @var int Goto below cell */
    public const GOTO_BELOW = 2;
    // --end--
    // Page layout in the PDF viewer - used by SetDisplayMode()
    public const LAYOUT_SINGLE = 'single';
    public const LAYOUT_CONTINUOUS = 'continuous';
    public const LAYOUT_TWO = 'two';
    public const LAYOUT_DEFAULT = 'default';
    // --end--
    // Page orientation - used by __construct() and AddPage()
    public const ORIENTATION_LANDSCAPE = 'L';
    public const ORIENTATION_PORTRAIT = 'P';
    // --end--
    // Page sizes - used by __construct() and AddPage()
    public const PAGE_SIZE_A3 = 'A3';
    public const PAGE_SIZE_A4 = 'A4';
    public const PAGE_SIZE_A5 = 'A5';
    public const PAGE_SIZE_LEGAL = 'Legal';
    public const PAGE_SIZE_LETTER = 'Letter';
    // --end--
    // Text alignment - used by Cell() and MultiCell()
    public const TEXT_ALIGN_CENTER = 'C';
    public const TEXT_ALIGN_CENTRE = 'C';
    public const TEXT_ALIGN_LEFT = 'L';
    public const TEXT_ALIGN_JUSTIFIED = 'J';
    public const TEXT_ALIGN_RIGHT = 'R';
    // --end--
    // Page measurement units - used by __construct()
    public const UNITS_POINTS = 'pt';
    public const UNITS_MILLIMETERS = 'mm';
    public const UNITS_CENTIMETERS = 'cm';
    public const UNITS_INCHES = 'in';
    // --end--
    public const UTF8 = true;
    // Page Zoom - used by SetDisplayMode()
    public const ZOOM_FULL_PAGE = 'fullpage';
    public const ZOOM_FULL_WIDTH = 'fullwidth';
    public const ZOOM_REAL = 'real';
    public const ZOOM_DEFAULT = 'default';
    // --end--

    /** @var string Default path to TTF fonts */
    private const TTF_FONTS = '@vendor/setasign/font/unifont';

    private array $customProperties = [];
    private string $name = '';
    private string $path = '';
    private bool $utf8 = false;

    public function __construct(
        string $class = FPDF::class,
        string $orientation = self::ORIENTATION_PORTRAIT,
        array|string $size = self::PAGE_SIZE_A4,
        string $unit = self::UNITS_MILLIMETERS,
        string $ttfFonts = ''
    )
    {
        if (!defined('_SYSTEM_TTFONTS')) {
            define(
                '_SYSTEM_TTFONTS',
                $ttfFonts !== '' ? $ttfFonts : self::TTF_FONTS . '/'
            );
        }

        $this->pdf = new ($class)($orientation, $unit, $size);
        $this
            ->pdf
            ->SetCreator($class)
        ;
    }

    public function __toString(): string
    {
        return $this
            ->pdf
            ->Output(self::DESTINATION_STRING, '', $this->utf8)
        ;
    }

    /**
     * @return string Name of the entity (person, organisation, ...) that created the document
     */
    public function getAuthor(): string
    {
        return $this->getMetadata('Author');
    }

    /**
     * @return string Name of the package used to create the document
     */
    public function getCreator(): string
    {
        return $this->getMetadata('Creator');
    }

    public function getCustomProperties(): array
    {
        return $this->customProperties;
    }

    public function getKeywords(): string
    {
        return $this->getMetadata('Keywords');
    }

    /**
     * @return string Name of the document when displayed in the browser, downloaded, or saved.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string Path of directory where document is saved.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return array|string The value of the metadata property or all properties
     */
    public function getMetadata(string $property = ''): array|string
    {
        return $this
            ->pdf
            ->getMetadata($property)
        ;
    }

    public function getSubject(): string
    {
        return $this->getMetadata('Subject');
    }

    public function getTitle(): string
    {
        return $this->getMetadata('Title');
    }

    public function isUTF8(): bool
    {
        return $this->utf8;
    }

    public function output(
        string $destination,
        DownloadResponseFactory $downloadResponseFactory
    ): bool|string|ResponseInterface
    {
        $return = false;

        if (str_contains($destination, self::DESTINATION_FILE)) {
            if ($this->getName() === '') {
                throw new RuntimeException(self::NAME_NOT_SET_EXCEPTION);
            }

            $destination = str_replace(self::DESTINATION_FILE, '', $destination);
            $path = $this->getPath();

            if (
                !is_dir($path)
                && !mkdir($path, 0766, true)
                && !is_dir($path)
            ) {
                throw new RuntimeException(sprintf(self::DIRECTORY_NOT_CREATED_EXCEPTION, $path));
            }

            $return = file_put_contents(
                $path . DIRECTORY_SEPARATOR . $this->getName(),
                (string)$this
            ) !== false;
        }

        if ((bool)$destination) {
            switch ($destination) {
                case self::DESTINATION_DOWNLOAD:
                    if ($this->getName() === '') {
                        throw new RuntimeException(self::NAME_NOT_SET_EXCEPTION);
                    }

                    return $downloadResponseFactory->sendContentAsFile(
                        (string)$this,
                        $this->getName(),
                        ContentDispositionHeader::ATTACHMENT,
                        self::MIME_TYPE
                    );
                    break;
                case self::DESTINATION_INLINE:
                    if ($this->getName() === '') {
                        throw new RuntimeException(self::NAME_NOT_SET_EXCEPTION);
                    }

                    return $downloadResponseFactory->sendContentAsFile(
                        (string)$this,
                        $this->getName(),
                        ContentDispositionHeader::INLINE,
                        self::MIME_TYPE
                    );
                    break;
                case self::DESTINATION_STRING:
                    return (string)$this;
                    break;
                default:
                    throw new InvalidArgumentException(self::INVALID_OUTPUT_DESTINATION_EXCEPTION);
            }
        }

        return $return;
    }

    public function withAuthor(string $author): DocumentInterface
    {
        $new = clone $this;
        $new
            ->pdf
            ->SetAuthor($author, $this->utf8)
        ;
        return $new;
    }

    public function withCreator(string $creator): DocumentInterface
    {
        $new = clone $this;
        $new
            ->pdf
            ->SetCreator($creator, $this->utf8)
        ;
        return $new;
    }

    public function withCustomProperties(array $customProperties): DocumentInterface
    {
        $new = clone $this;
        $new
            ->pdf
            ->SetCustomProperties($customProperties)
        ;
        return $new;
    }

    public function withKeywords(string ...$keywords): DocumentInterface
    {
        $new = clone $this;
        $new
            ->pdf
            ->SetKeywords(implode(', ', $keywords), $this->utf8)
        ;
        return $new;
    }

    public function withName(string $name): DocumentInterface
    {
        $new = clone $this;
        $new->name = $name;
        return $new;
    }

    public function withPath(string $path): DocumentInterface
    {
        $new = clone $this;
        $new->path = $path;
        return $new;
    }

    public function withSubject(string $subject): DocumentInterface
    {
        $new = clone $this;
        $new
            ->pdf
            ->SetSubject($subject, $this->utf8)
        ;
        return $new;
    }

    public function withTitle(string $title): DocumentInterface
    {
        $new = clone $this;
        $new
            ->pdf
            ->SetTitle($title, $this->utf8)
        ;
        return $new;
    }

    public function withUTF8(bool $utf8): DocumentInterface
    {
        $new = clone $this;
        $new->utf8 = $utf8;
        return $new;
    }
}
