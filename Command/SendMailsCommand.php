<?php
namespace Jasuwienas\MailBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class SendMailsCommand extends ContainerAwareCommand
{


    protected function configure()
    {
        $this
            ->setName('mail:send')
            ->setDescription('Sends mails from queue.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $container->get('mail.queue_manager.process')->run();
    }

}