<?php

namespace Phpactor\Extension\CompletionWorse\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\Completion\Core\Completor;
use Phpactor\Container\Container;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\ClassToFile\ClassToFileExtension;
use Phpactor\Extension\CompletionWorse\CompletionWorseExtension;
use Phpactor\Extension\Completion\CompletionExtension;
use Phpactor\Extension\ComposerAutoloader\ComposerAutoloaderExtension;
use Phpactor\Extension\ReferenceFinder\ReferenceFinderExtension;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\FilePathResolverExtension\FilePathResolverExtension;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;
use RuntimeException;

class CompletionWorseExtensionTest extends TestCase
{
    public function testBuild()
    {
        $container = $this->buildContainer();

        $completor = $container->get(CompletionExtension::SERVICE_REGISTRY)->completorForType('php');
        $this->assertInstanceOf(Completor::class, $completor);
        assert($completor instanceof Completor);

        $completor->complete(
            TextDocumentBuilder::create('<?php array')->build(),
            ByteOffset::fromInt(8)
        );
    }

    public function testDisableCompletors()
    {
        $container = $this->buildContainer([
            CompletionWorseExtension::PARAM_DISABLED_COMPLETORS => [
                'worse_constructor',
            ],
        ]);
        $completors = $container->get('completion_worse.completor_map');

        self::assertFalse(in_array('completion_worse.completor.constructor', $completors), 'Completor disabled');
    }

    public function testExceptionWhenDisabledCompletorNotExisting()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown completors');
        $container = $this->buildContainer([
            CompletionWorseExtension::PARAM_DISABLED_COMPLETORS => [
                'foobar',
            ],
        ]);
        $container->get('completion_worse.completor_map');
    }

    private function buildContainer(array $config = []): Container
    {
        return PhpactorContainer::fromExtensions(
            [
            CompletionExtension::class,
            FilePathResolverExtension::class,
            ClassToFileExtension::class,
            ComposerAutoloaderExtension::class,
            LoggingExtension::class,
            WorseReflectionExtension::class,
            CompletionWorseExtension::class,
            SourceCodeFilesystemExtension::class,
            ReferenceFinderExtension::class,
        ],
            array_merge([
                FilePathResolverExtension::PARAM_APPLICATION_ROOT => __DIR__,
            ], $config)
        );
    }
}
