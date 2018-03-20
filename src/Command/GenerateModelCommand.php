<?php declare(strict_types=1);

namespace Shyim\DatabaseEntitiesBuilder\Command;

use Shyim\DatabaseEntitiesBuilder\Generator;
use Shyim\DatabaseEntitiesBuilder\Structs\Request;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class GenerateModelCommand
 * @author Soner Sayakci <shyim@posteo.de>
 */
class GenerateModelCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @author Soner Sayakci <shyim@posteo.de>
     */
    protected function configure()
    {
        $this
            ->setName('shy:model:generate')
            ->addArgument('namespace', InputArgument::REQUIRED, 'Namespace that should be used "Example\\Models"')
            ->addArgument('target', InputArgument::REQUIRED, 'Target directory (should be exists)')
            ->addOption('filter', 'f', InputOption::VALUE_OPTIONAL, 'Filter tables which should be generated')
            ->addOption('php-version', 'p', InputOption::VALUE_OPTIONAL, 'min PHP Version (php70, php71)', 'php71')
            ->addOption('prefix-remove', null, InputOption::VALUE_OPTIONAL, 'Remove prefix from entity, example s_core');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @author Soner Sayakci <shyim@posteo.de>
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $request = new Request();
        $request->folder = $input->getArgument('target');
        $request->namespace = $input->getArgument('namespace');
        $request->filter = $input->getOption('filter');
        $request->phpVersion = $input->getOption('php-version');
        $request->removePrefixEntity = $input->getOption('prefix-remove');

        $this->container->get(Generator::class)->generateModels($request);

        $io = new SymfonyStyle($input, $output);
        $io->success('Models generated');
    }
}
