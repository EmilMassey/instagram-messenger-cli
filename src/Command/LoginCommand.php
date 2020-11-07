<?php

namespace InstagramMessenger\Command;

use GuzzleHttp\Client;
use InstagramScraper\Exception\InstagramAuthException;
use InstagramScraper\Exception\InstagramChallengeRecaptchaException;
use InstagramScraper\Instagram;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;


final class LoginCommand extends Command
{
    private CacheInterface $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('login')
            ->setDescription('Login to Instagram.')
            ->setHelp('This command authenticates you with Instagram. You only need to run it once.')
            ->addOption('user', 'u', InputOption::VALUE_REQUIRED, 'Username')
            ->addOption('password','p',  InputOption::VALUE_REQUIRED, 'User password');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($password = $input->getOption('password')) {
            $output->writeln('<comment>Warning: it is not safe to pass password as an option!</comment>');
        }

        $questionHelper = $this->getHelper('question');

        if (!$user = $input->getOption('user')) {
            $question = new Question('Please enter username: ');
            $question->setTrimmable(true);
            $question->setValidator(function ($value) {
                if (trim($value) == '') {
                    throw new \Exception('Username cannot be empty');
                }

                return $value;
            });

            $user = $questionHelper->ask($input, $output, $question);
        }

        if (!$password) {
            $question = new Question('Please enter user password: ');
            $question->setHidden(true);
            $question->setValidator(function ($value) {
                if (trim($value) == '') {
                    throw new \Exception('The password cannot be empty');
                }

                return $value;
            });

            $password = $questionHelper->ask($input, $output, $question);
        }

        $instagram = Instagram::withCredentials(new Client(), $user, $password, $this->cache);

        try {
            $instagram->login(true);
        } catch (InstagramAuthException $exception) {
            $output->writeln('<error>Invalid credentials</error>');

            return Command::FAILURE;
        } catch (InstagramChallengeRecaptchaException $exception) {
            $output->writeln('<error>Instagram asked to enter the captcha. Open Instagram in a browser and solve the captcha</error>');

            return Command::FAILURE;
        }

        $this->cache->set('user', $user);
        $instagram->saveSession();

        $output->writeln('<info>You are successfully logged in!</info>');

        return Command::SUCCESS;
    }
}
