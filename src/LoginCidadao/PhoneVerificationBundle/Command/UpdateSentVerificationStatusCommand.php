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

use LoginCidadao\PhoneVerificationBundle\Service\SmsStatusService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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

        /** @var SmsStatusService $updater */
        $updater = $this->getContainer()->get('phone_verification.sms_status');
        $updater->setSymfonyStyle($io);

        $io->section('Updating messages\' status');
        $updater->updateSentVerificationStatus(100);

        $io->section('Average delivery time');
        $avg = $updater->getAverageDeliveryTime(10);
        $io->text("The average delivery time is {$avg} seconds");

        $io->success('Finished updating statuses');
    }
}
