<?php

namespace InstagramMessenger\Printer;

use InstagramMessenger\Helper\UserHelper;
use InstagramScraper\Model\ThreadItem;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class HumanReadableMessagePrinter implements MessagePrinterInterface
{
    private CacheInterface $cache;
    private UserHelper $userHelper;

    public function __construct(CacheInterface $cache, UserHelper $userHelper)
    {
        $this->cache = $cache;
        $this->userHelper = $userHelper;
    }

    public function print(ThreadItem $message, OutputInterface $output): void
    {
        $username = $this->userHelper->getUsernameById($message->getUserId());
        $isSelf = $username === $this->cache->get('user');

        $header = \sprintf(
            '%s | %s',
            self::getFormattedDateString($message->getTime()),
            $username ?: $message->getUserId()
        );

        switch ($message->getType()) {
            case 'link':
                $text = '--- LINK ---';
                break;
            case 'story_share':
                $text = '--- STORY ---';
                break;
            case 'media_share':
                $text = '--- MEDIA ---';
                break;
            case 'reel_share':
                if (!$text = $message->getReelShare()->getText()) {
                    $text = '--- REEL SHARE ---';
                }
                break;
            default:
                $text = $message->getText();
        }

        if ($isSelf) {
            $output->writeln(\sprintf('%s%s%s', $header, PHP_EOL, $text));
        } else {
            $output->writeln(\sprintf('<info>%s%s%s</info>', $header, PHP_EOL, $text));
        }
    }

    private static function getFormattedDateString(int $timestamp): string
    {
        return \DateTimeImmutable::createFromFormat('U', $timestamp)->format('Y-m-d H:i');
    }
}
