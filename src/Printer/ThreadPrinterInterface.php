<?php

namespace InstagramMessenger\Printer;

use InstagramScraper\Model\Thread;
use InstagramScraper\Model\ThreadItem;
use Symfony\Component\Console\Output\OutputInterface;

interface ThreadPrinterInterface
{
    public function print(Thread $thread, OutputInterface $output): void;
}
