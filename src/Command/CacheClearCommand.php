<?php

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

        foreach (new \DirectoryIterator($realCacheDir) as $file) {
            var_dump(get_class($file));
//            if (is_file($str)) {
//                return @unlink($str);
//            }
//            elseif (is_dir($str)) {
//                $scan = glob(rtrim($str,'/').'/*');
//                foreach($scan as $index=>$path) {
//                    recursiveDelete($path);
//                }
//                return @rmdir($str);
//            }
        }


        return Command::SUCCESS;
    }
}
