<?php

declare(strict_types=1);

namespace WeDevelop\AuditLog\Tests\Functional;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Kernel;
use WeDevelop\AuditLog\AuditLogBundle;
use WeDevelop\AuditLog\Reading\RecordReader;
use WeDevelop\AuditLog\Recording\Recorder;

use function dirname;
use function sys_get_temp_dir;

final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    /**
     * Symfony 8.1 deprecated HttpKernel's BundleInterface, which the inherited
     * return type names. Narrow to the concrete Bundle base class our test
     * bundles all extend — accurate, deprecation-free, and valid on 8.0 and 8.1.
     *
     * @return iterable<Bundle>
     */
    #[Override]
    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new SecurityBundle(),
            new DoctrineBundle(),
            new AuditLogBundle(),
        ];
    }

    #[Override]
    public function getProjectDir(): string
    {
        return dirname(__DIR__, 2);
    }

    #[Override]
    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/audit-log-test/cache/'.$this->environment;
    }

    #[Override]
    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/audit-log-test/log';
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'test' => true,
            'secret' => 'test',
            'http_method_override' => false,
            'handle_all_throwables' => true,
            'php_errors' => ['log' => true],
        ]);

        $container->extension('security', [
            'providers' => ['in_memory' => ['memory' => null]],
            'firewalls' => ['main' => ['security' => false]],
        ]);

        $container->extension('doctrine', [
            'dbal' => ['driver' => 'pdo_sqlite', 'url' => 'sqlite:///:memory:'],
            'orm' => [],
        ]);

        // Expose the services the test needs to reach. AliasConfigurator is not
        // chainable in Symfony 8, so each alias is registered as its own statement.
        $services = $container->services();
        $services->alias('test.recorder', Recorder::class)->public();
        $services->alias('test.reader', RecordReader::class)->public();
        $services->alias('test.em', EntityManagerInterface::class)->public();
    }
}
