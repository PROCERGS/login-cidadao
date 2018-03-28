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
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\ValidationBundle\Validator\Constraints\MobilePhoneNumberValidator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class BlockUserCommand extends AbstractPersonBlockCommand
{

    protected function configure()
    {
        parent::configure();
        $this
            ->setName('lc:block-user')
            ->addArgument('user', InputArgument::REQUIRED, "User's Email, CPF, username or ID")
            ->setDescription("Block the user found");
    }

    protected function getUsers(SymfonyStyle $io, InputInterface $input, OutputInterface $output)
    {
        $io->section("Searching users...");

        $userManager = $this->getUserManager();
        $query = $input->getArgument('user');
        $user = $userManager->findUserByUsernameOrEmail($query);
        if (!$user instanceof PersonInterface) {
            $user = $userManager->findUserBy(['id' => $query]);
        }

        return [$user];
    }
}
