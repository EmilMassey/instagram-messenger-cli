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
                $text = $this->getReelShareText($message);
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

    private function getReelShareText(ThreadItem $message): string
    {
        $reelShare = $message->getReelShare();
        $peer = $this->userHelper->getUsernameById($message->getUserId());
        $owner = $this->userHelper->getUsernameById($reelShare->getOwnerId());

        $isSelfOwner = $owner === $this->cache->get('user');

        switch ($reelShare->getType()) {
            case 'mention':
                $mentioned = $this->userHelper->getUsernameById($reelShare->getMentionedId());

                if ($isSelfOwner) {
                    $text = \sprintf('You mentioned %s in your story', $mentioned);
                } else {
                    $text = \sprintf('%s mentioned you in her/his story', $owner);
                }
                break;
            case 'reply':
                if ($isSelfOwner) {
                    $text = \sprintf('%s replied to your story', $peer);
                } else {
                    $text = \sprintf('You replied to her/his story');
                }
                break;
            default:
                $text = $reelShare->getText() ?: '--- REEL SHARE ---';
        }

        if (null !== $reelShare->getMedia() && null !== $image = $reelShare->getMedia()->getImage()) {
            $text .= \sprintf(': <href=%s>%s</>', $image, $image);
        }

        if ('reply' && $reelShare->getType() && $reelShare->getText()) {
            $text .= \sprintf('%s%s%s', PHP_EOL, PHP_EOL, $reelShare->getText());
        }

        return $text;
    }

    private static function getFormattedDateString(int $timestamp): string
    {
        return \DateTimeImmutable::createFromFormat('U', $timestamp)->format('Y-m-d H:i');
    }
}
