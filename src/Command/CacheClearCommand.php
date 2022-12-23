<?php
declare(strict_types=1);

namespace Pulsar\Core\Command;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CacheClearCommand extends Command
{
    protected static $defaultName = 'cache:clear';
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct(null);
        $this->container = $container;
    }

    protected function configure()
    {
        $this->setDescription('Clear the cache');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $realCacheDir = $this->container->get('pulsar.cache_dir');
        if (!is_writable($realCacheDir)) {
            throw new \RuntimeException(sprintf('Unable to write in the "%s" directory.', $realCacheDir));
        }

        $io->comment('Clearing the cache : ' . $realCacheDir);

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($realCacheDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        /**
         * @var \SplFileInfo $file
         */
        foreach ($files as $file) {
            if ($file->getFilename() === '.gitignore') {
                continue;
            }
            if ($file->isFile()) {
                if (!unlink($file->getPathname())) {
                    throw new \RuntimeException("Failed to unlink {$file->getPathname()} : " . var_export(error_get_last(), true));
                }
            }elseif ($file->isDir()) {
                if (!rmdir($file->getPathname())) {
                    throw new \RuntimeException("Failed to remove {$file->getPathname()} : " . var_export(error_get_last(), true));
                }
            }
        }

        $io->success('Cache was successfully cleared.');

        return Command::SUCCESS;
    }
}
