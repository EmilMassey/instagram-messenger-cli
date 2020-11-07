<?php

namespace InstagramMessenger\Command;

use GuzzleHttp\Client;
use InstagramScraper\Exception\InstagramAuthException;
use InstagramScraper\Instagram;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractAuthenticatedCommand extends Command
{
    protected CacheInterface $cache;
    protected Instagram $instagram;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$user = $this->cache->get('user')) {
            $this->writeNotAuthenticatedError($output);

            return Command::FAILURE;
        }

        $this->instagram = Instagram::withCredentials(new Client(), $user, 'dummy', $this->cache);

        try {
            $this->instagram->login();
        } catch (InstagramAuthException $exception) {
            $this->writeNotAuthenticatedError($output);

            return Command::FAILURE;
        }

        return 0;
    }

    protected function writeNotAuthenticatedError(OutputInterface $output): void
    {
        $output->writeln('<error>You are not authenticated. Please run "login" command first.</error>');
    }
}
