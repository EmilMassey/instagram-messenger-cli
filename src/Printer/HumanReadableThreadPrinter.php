<?php

namespace InstagramMessenger\Printer;

use InstagramScraper\Model\Thread;
use Symfony\Component\Console\Output\OutputInterface;

final class HumanReadableThreadPrinter implements ThreadPrinterInterface
{
    private const SKIPPED_MESSAGE_TYPES = ['action_log'];

    private MessagePrinterInterface $messagePrinter;
    private string $separator;

    public function __construct(
        MessagePrinterInterface $messagePrinter,
        string $separator = '------------'
    ) {
        $this->messagePrinter = $messagePrinter;
        $this->separator = $separator;
    }

    public function print(Thread $thread, OutputInterface $output): void
    {
        for ($i = \count($thread->getItems()) - 1; $i >= 0; $i--) {
            $message = $thread->getItems()[$i];

            if (\in_array($message->getType(), self::SKIPPED_MESSAGE_TYPES, true)) {
                continue;
            }

            $this->messagePrinter->print($message, $output);
            $output->writeln($this->separator);
        }
    }

    public function setSeparator(string $separator): void
    {
        $this->separator = $separator;
    }
}
