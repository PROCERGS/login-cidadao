<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Command;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\ValidationBundle\Validator\Constraints\MobilePhoneNumberValidator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class BlockByPhoneCommand
 * @package LoginCidadao\CoreBundle\Command
 * @codeCoverageIgnore
 */
class BlockByPhoneCommand extends AbstractPersonBlockCommand
{

    protected function configure()
    {
        parent::configure();
        $this
            ->setName('lc:block-by-phone')
            ->addArgument('phone', InputArgument::REQUIRED, 'Mobile number in the E.164 format.')
            ->addOption(
                'ignore-mobile-validation',
                'i',
                InputOption::VALUE_NONE,
                'Disable the mobile phone validation so you can pass a non-mobile phone')
            ->setDescription("Blocks all users that are using the given mobile phone.");
    }

    protected function getUsers(SymfonyStyle $io, InputInterface $input, OutputInterface $output)
    {
        $phone = $this->getValidPhone($io, $input);
        if (!$phone instanceof PhoneNumber) {
            return [];
        }

        $io->section("Searching users...");
        /** @var PersonInterface[] $users */
        $users = $this->getEntityManager()
            ->getRepository('LoginCidadaoCoreBundle:Person')
            ->findBy(['mobile' => $phone, 'enabled' => true]);

        return $users;
    }

    private function getValidPhone(SymfonyStyle $io, InputInterface $input)
    {
        $phoneArg = $input->getArgument('phone');
        $phoneUtil = $this->getPhoneUtil();
        $checkMobile = !$input->getOption('ignore-mobile-validation');

        try {
            $phone = $phoneUtil->parse($phoneArg);

            if ($checkMobile && false === MobilePhoneNumberValidator::isMobile($phone)) {
                $io->error('The given phone is not a mobile phone...');

                return null;
            }
        } catch (NumberParseException $e) {
            $io->error("'{$phoneArg}' doesn't appear to be a valid phone number.");

            return null;
        }

        return $phone;
    }
}
