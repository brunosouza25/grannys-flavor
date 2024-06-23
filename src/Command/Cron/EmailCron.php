<?php

namespace App\Command\Cron;

use App\Service\EmailService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:send-emails-from-database',
    description: 'Send emails from database.',
    hidden: false,
    aliases: ['app:send-emails']
)]
class EmailCron extends Command
{
    private $emailService;
    public function __construct(EmailService $emailService)
    {
        parent::__construct();
        $this->emailService = $emailService;
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->emailService->sendEmailsInDataBase();
        return Command::SUCCESS;
    }
}