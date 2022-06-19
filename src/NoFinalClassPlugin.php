<?php

namespace EasyCorp\Bundle\EasyAdminBundle;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Factory;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;

final class NoFinalClassPlugin implements PluginInterface, EventSubscriberInterface
{
    private IOInterface $io;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->io = $io;
    }

    public static function getSubscribedEvents()
    {
        return [
            PackageEvents::POST_PACKAGE_INSTALL => 'onPackageInstall',
            PackageEvents::POST_PACKAGE_UPDATE => 'onPackageUpdate',
        ];
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
    }

    public function onPackageInstall(PackageEvent $event)
    {
        if (!$this->isComposerWorkingOn('easycorp/easyadmin-bundle', $event) && !$this->isComposerWorkingOn('easycorp/easyadmin-no-final-plugin', $event)) {
            return;
        }

        $this->removeFinalFromAllEasyAdminClasses();
    }

    public function onPackageUpdate(PackageEvent $event)
    {
        if (!$this->isComposerWorkingOn('easycorp/easyadmin-bundle', $event)) {
            return;
        }

        $this->removeFinalFromAllEasyAdminClasses();
    }

    public function removeFinalFromAllEasyAdminClasses()
    {
        $vendorDirPath = $this->getVendorDirPath();
        $easyAdminDirPath = $vendorDirPath.'/easycorp/easyadmin-bundle';
        foreach ($this->getFilePathsOfAllEasyAdminClasses($easyAdminDirPath) as $filePath) {
            file_put_contents(
                $filePath,
                str_replace('final class ', 'class ', file_get_contents($filePath)),
                flags: \LOCK_EX
            );
        }

        $this->io->write('Updated all EasyAdmin PHP files to make classes non-final');
    }

    private function isComposerWorkingOn(string $packageName, PackageEvent $event): bool
    {
        /** @var PackageInterface|null $package */
        $package = null;

        foreach ($event->getOperations() as $operation) {
            if ('install' === $operation->getOperationType()) {
                /** @var InstallOperation $operation */
                $package = $operation->getPackage();
            } elseif ('update' === $operation->getOperationType()) {
                /** @var UpdateOperation $operation */
                $package = $operation->getInitialPackage();
            }
        }

        return $packageName === $package?->getName();
    }

    private function getVendorDirPath(): string
    {
        $composerJsonFilePath = Factory::getComposerFile();
        $composerJsonContents = json_decode(file_get_contents($composerJsonFilePath), associative: true, flags: JSON_THROW_ON_ERROR);
        $projectDir = dirname(realpath($composerJsonFilePath));

        return $composerJsonContents['config']['vendor-dir'] ?? $projectDir.'/vendor';
    }

    /**
     * @return iterable Returns the file paths of all PHP files that contain EasyAdmin classes
     */
    private function getFilePathsOfAllEasyAdminClasses(string $easyAdminDirPath): iterable
    {
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($easyAdminDirPath, \FilesystemIterator::SKIP_DOTS)) as $filePath) {
            if (is_dir($filePath) || !str_ends_with($filePath, '.php')) {
                continue;
            }

            yield $filePath;
        }
    }
}
