<?php

namespace InstagramMessenger\Printer;

final class ThreadPrinterFactory
{
    private array $printers = [];

    public function addPrinter(string $name, ThreadPrinterInterface $printer): void
    {
        $this->printers[$name] = $printer;
    }

    public function getPrinter(string $name): ThreadPrinterInterface
    {
        if (!\array_key_exists($name, $this->printers)) {
            throw new \InvalidArgumentException(\sprintf('Invalid printer: "%s"', $name));
        }

        return $this->printers[$name];
    }
}
