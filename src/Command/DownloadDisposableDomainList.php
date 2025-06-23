<?php

namespace Pantono\Email\Command;

use Symfony\Component\Console\Command\Command;
use Pantono\Email\EmailAddresses;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

class DownloadDisposableDomainList extends Command
{
    private EmailAddresses $emailAddresses;

    public function __construct(EmailAddresses $emailAddresses)
    {
        $this->emailAddresses = $emailAddresses;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('email:download-disposable-list')
            ->addOption('list', 'l', InputArgument::OPTIONAL, 'URL of the list to synchronise', EmailAddresses::DISPOSABLE_LIST);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = $input->getOption('list');
        $output->write('Syncing disposable e-mail list');
        $this->emailAddresses->syncDisposableList($file);
        $output->writeln('Done');
        return 0;
    }
}
