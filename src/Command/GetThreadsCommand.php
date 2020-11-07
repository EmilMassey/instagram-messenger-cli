<?php

namespace InstagramMessenger\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class GetThreadsCommand extends AbstractAuthenticatedCommand
{
    protected function configure()
    {
        $this
            ->setName('threads')
            ->setDescription('Get threads.')
            ->setHelp('This command shows you newest threads')
            ->addOption('count', null, InputOption::VALUE_REQUIRED, 'Threads count', 10);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (0 !== $returnCode = parent::execute($input, $output)) {
            return $returnCode;
        }

        $count = $input->getOption('count');
        $threads =  $this->instagram->getThreads($count, $count, 1);

        foreach ($threads as $thread) {
            if ($thread->isGroup()) {
                // currently not supported
                continue;
            }

            if (!$thread->getReadState()) {
                $output->writeln($thread->getTitle());
            } else {
                $output->writeln(\sprintf('<info>-> %s</info>', $thread->getTitle()));
            }
        }

        return Command::SUCCESS;
    }
}
