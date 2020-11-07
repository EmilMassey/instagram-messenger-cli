<?php

namespace InstagramMessenger\Command;

use GuzzleHttp\Client;
use InstagramMessenger\Enricher\ThreadItemEnricher;
use InstagramMessenger\Helper\UserHelper;
use InstagramMessenger\Printer\ThreadPrinterFactory;
use InstagramMessenger\Printer\ThreadPrinterInterface;
use InstagramScraper\Exception\InstagramAuthException;
use InstagramScraper\Instagram;
use InstagramScraper\Model\Thread;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class GetThreadCommand extends AbstractAuthenticatedCommand
{
    private ThreadPrinterFactory $printerFactory;

    public function __construct(CacheInterface $cache, ThreadPrinterFactory $printerFactory)
    {
        $this->printerFactory = $printerFactory;
        parent::__construct($cache);
    }

    protected function configure()
    {
        $this
            ->setName('thread')
            ->setDescription('Get a thread.')
            ->setHelp('This command shows you messages from a thread')
            ->addArgument('peer', InputArgument::REQUIRED, 'Peer\'s username')
            ->addOption('count', null, InputOption::VALUE_REQUIRED, 'Messages count', 10)
            ->addOption('printer', 'p', InputOption::VALUE_REQUIRED, 'Printer type', 'human');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (0 !== $returnCode = parent::execute($input, $output)) {
            return $returnCode;
        }

        try {
            $printerFactory = $this->printerFactory->getPrinter($input->getOption('printer'));
        } catch (\InvalidArgumentException $exception) {
            $output->writeln(\sprintf('<error>%s</error>', $exception->getMessage()));

            return Command::FAILURE;
        }

        // Todo: use getPaginateThreads
        $threads =  $this->instagram->getThreads(20, 20, $input->getOption('count'));

        foreach ($threads as $thread) {
            if ($thread->getTitle() !== $input->getArgument('peer')) {
                continue;
            }

            $printerFactory->print($thread, $output);

            return Command::SUCCESS;
        }

        $output->writeln(\sprintf('<error>Could not find a thread with user "%s"</error>', $input->getArgument('peer')));

        return Command::FAILURE;
    }
}
