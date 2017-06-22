<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\PhoneVerificationBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\PhoneVerificationBundle\Entity\SentVerificationRepository;
use LoginCidadao\PhoneVerificationBundle\Service\SmsStatusService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UpdateSentVerificationStatusCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('lc:phone-verification:update-sent-status')
            ->setDescription('Updates the status of SentVerification entities');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Update Verification Messages Status');

        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->getContainer()->get('event_dispatcher');

        /** @var EntityManagerInterface $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        /** @var SentVerificationRepository $repo */
        $repo = $em->getRepository('LoginCidadaoPhoneVerificationBundle:SentVerification');

        $updater = new SmsStatusService($dispatcher, $repo, $io);
        $io->section('Updating messages\' status');
        $updater->updateSentVerificationStatus($em);

        $io->section('Average delivery time');
        $avg = $updater->getAverageDeliveryTime(10);
        $io->text("The average delivery time is {$avg} seconds");

        $io->success('Finished updating statuses');
    }
}
