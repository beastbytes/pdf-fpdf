<?php
/**
 * @copyright Copyright © 2023 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\PDF\FPDF\Tests\Support;

use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\StreamFactory;
use BeastBytes\PDF\DocumentFactory;
use BeastBytes\PDF\DocumentFactoryInterface;
use BeastBytes\PDF\DocumentGenerator;
use BeastBytes\PDF\PdfInterface;
use BeastBytes\PDF\Pdf;
use BeastBytes\PDF\FPDF\Document;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use ReflectionClass;
use Yiisoft\ResponseDownload\DownloadResponseFactory;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Test\Support\EventDispatcher\SimpleEventDispatcher;
use Yiisoft\View\View;
use Yiisoft\View\ViewContext;

use const DIRECTORY_SEPARATOR;

class TestCase extends \PHPUnit\Framework\TestCase
{
    private ?ContainerInterface $container = null;

    protected function get(string $id)
    {
        return $this
            ->getContainer()
            ->get($id);
    }

    protected static function getTestFilePath(): string
    {
        return sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . basename(str_replace('\\', '_', static::class))
        ;
    }

    private function getContainer(): ContainerInterface
    {
        if ($this->container === null) {
            $viewDir = __DIR__ . DIRECTORY_SEPARATOR . 'view';
            $eventDispatcher = new SimpleEventDispatcher();
            $view = new View($viewDir, $eventDispatcher);
            $viewContext = new ViewContext($viewDir);
            $documentGenerator = new DocumentGenerator($view, $viewContext);
            $documentFactory = new DocumentFactory(Document::class, []);
            $downloadResponseFactory = new DownloadResponseFactory(new ResponseFactory(), new StreamFactory());

            $this->container = new SimpleContainer([
                EventDispatcherInterface::class => $eventDispatcher,
                PdfInterface::class => new Pdf(
                    $documentFactory,
                    $documentGenerator,
                    $eventDispatcher,
                    $downloadResponseFactory
                ),
                DocumentGenerator::class => new DocumentGenerator($view, $viewContext),
                DocumentFactoryInterface::class => $documentFactory,
                View::class => $view,
                ViewContext::class => $viewContext,
            ]);
        }

        return $this->container;
    }

    /**
     * Gets an inaccessible object property.
     *
     * @param object $object
     * @param string $propertyName
     * @return mixed
     */
    protected function getInaccessibleProperty(object $object, string $propertyName): mixed
    {
        $class = new ReflectionClass($object);

        while (!$class->hasProperty($propertyName)) {
            $class = $class->getParentClass();
        }

        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        $result = $property->getValue($object);
        $property->setAccessible(false);

        return $result;
    }

    protected function saveFile(string $filename, string $data): void
    {
        $path = dirname($filename);

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        file_put_contents($filename, $data);
    }
}
