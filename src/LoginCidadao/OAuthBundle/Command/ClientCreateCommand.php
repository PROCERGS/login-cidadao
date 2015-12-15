<?php

namespace LoginCidadao\OAuthBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use LoginCidadao\OAuthBundle\Entity\Client;

class ClientCreateCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
                ->setName('lc:oauth-server:client:create')
                ->setDescription('Creates a new client')
                ->addArgument('name', InputArgument::REQUIRED, 'Sets the client name', null)
                ->addArgument('description', InputArgument::REQUIRED, 'Sets the client description', null)
                ->addArgument('site-url', InputArgument::REQUIRED, 'Sets the client main URL', null)
                ->addOption('redirect-uri', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Sets redirect uri for client. Use this option multiple times to set multiple redirect URIs.', null)
                ->addOption('grant-type', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Sets allowed grant type for client. Use this option multiple times to set multiple grant types..', null)
                ->setHelp(<<<EOT
The <info>%command.name%</info>command creates a new client.

  <info>php %command.full_name% [--redirect-uri=...] [--grant-type=...] name</info>

EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $clientManager = $this->getContainer()->get('fos_oauth_server.client_manager');
        $client = $clientManager->createClient();
        $client->setName($input->getArgument('name'));
        $client->setDescription($input->getArgument('description'));
        $client->setSiteUrl($input->getArgument('site-url'));
        $client->setRedirectUris($input->getOption('redirect-uri'));
        $client->setAllowedGrantTypes($input->getOption('grant-type'));
        $clientManager->updateClient($client);
        $output->writeln(sprintf('Added a new client with name <info>%s</info> and public id <info>%s</info>.', $client->getName(), $client->getPublicId()));
    }

}
