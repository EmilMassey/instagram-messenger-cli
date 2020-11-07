<?php

namespace InstagramMessenger\Printer;

use InstagramScraper\Model\ThreadItem;
use Symfony\Component\Console\Output\OutputInterface;

interface MessagePrinterInterface
{
    public function print(ThreadItem $message, OutputInterface $output): void;
}
