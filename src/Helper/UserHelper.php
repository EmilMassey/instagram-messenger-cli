<?php

namespace InstagramMessenger\Helper;

use GuzzleHttp\Client;
use InstagramScraper\Exception\InstagramNotFoundException;
use InstagramScraper\Instagram;
use Psr\SimpleCache\CacheInterface;

final class UserHelper
{
    /**
     * @var CacheInterface
     */
    private CacheInterface $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function getUsernameById(string $id): ?string
    {
        if ($username = $this->cache->get('user_'.$id)) {
            return $username;
        }

        $instagram = new Instagram(new Client());

        try {
            $username = $instagram->getUsernameById($id);
        } catch (InstagramNotFoundException $exception) {
            return null;
        }

        $this->cache->set('user_'.$id, $username);

        return $username;
    }
}
