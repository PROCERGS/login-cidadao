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

use Doctrine\ORM\EntityManagerInterface;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use LoginCidadao\CoreBundle\Entity\PersonRepository;
use LoginCidadao\CoreBundle\Mailer\TwigSwiftMailer;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\CoreBundle\Security\User\Manager\UserManager;
use LoginCidadao\ValidationBundle\Validator\Constraints\MobilePhoneNumberValidator;
use SimpleThings\EntityAudit\AuditConfiguration;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class BlockByPhoneCommand extends ContainerAwareCommand
{
    /** @var PhoneNumberUtil */
    private $phoneUtil;

    protected function configure()
    {
        $this
            ->setName('lc:block-by-phone')
            ->addArgument('phone', InputArgument::REQUIRED, 'Mobile number in the E.164 format.')
            ->addOption(
                'ignore-mobile-validation',
                'i',
                InputOption::VALUE_NONE,
                'Disable the mobile phone validation so you can pass a non-mobile phone')
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Do NOT persist changes.')
            ->setDescription("Blocks all users that are using the given mobile phone.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title("Blocking users by Phone Number");

        $phoneArg = $input->getArgument('phone');
        $phoneUtil = $this->getPhoneUtil();
        $checkMobile = !$input->getOption('ignore-mobile-validation');
        $dryRun = $input->getOption('dry-run');
        try {
            $phone = $phoneUtil->parse($phoneArg);

            if ($checkMobile && false === MobilePhoneNumberValidator::isMobile($phone)) {
                $io->error('The given phone is not a mobile phone...');

                return;
            }
        } catch (NumberParseException $e) {
            $io->error("'{$phoneArg}' doesn't appear to be a valid phone number.");

            return;
        }

        if (!$users = $this->findUsers($io, $phone)) {
            return;
        }

        $this->prepareAudit();

        $blockedUsers = $this->blockUsers($io, $users, $dryRun);
        $this->listBlocked($io, $blockedUsers);

        if (!empty($blockedUsers)) {
            $this->notifyUsers($io, $blockedUsers, $dryRun);
        }
    }

    /**
     * @return PersonRepository
     */
    private function getPersonRepository()
    {
        return $this->getEntityManager()->getRepository('LoginCidadaoCoreBundle:Person');
    }

    /**
     * @return EntityManagerInterface|object
     */
    private function getEntityManager()
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    private function getPhoneUtil()
    {
        if (!$this->phoneUtil instanceof PhoneNumberUtil) {
            $this->phoneUtil = PhoneNumberUtil::getInstance();
        }

        return $this->phoneUtil;
    }

    /**
     * @param SymfonyStyle $io
     * @param PhoneNumber $phone
     * @return bool|PersonInterface[]
     */
    private function findUsers(SymfonyStyle $io, PhoneNumber $phone)
    {
        $personRepo = $this->getPersonRepository();

        $io->section("Searching users...");
        /** @var PersonInterface[] $users */
        $users = $personRepo->findBy(['mobile' => $phone]);

        $count = count($users);
        if ($count === 0) {
            $io->note("No users found...");

            return false;
        }

        return $users;
    }

    /**
     * @param SymfonyStyle $io
     * @param PersonInterface[] $users
     * @param bool $dryRun
     * @return array
     */
    private function blockUsers(SymfonyStyle $io, $users, $dryRun = true)
    {
        $count = count($users);

        $io->section("Blocking {$count} users...");

        if ($dryRun) {
            $io->note("This is a DRY RUN. Users won't actually be blocked!");
        } elseif (!$io->confirm("Are you sure you want to BLOCK {$count} users?", false)) {
            return [];
        }

        $userManager = $this->getUserManager();
        $io->progressStart($count);
        $blockedUsers = [];
        foreach ($users as $user) {
            $user = $userManager->blockPerson($user);
            if ($user instanceof PersonInterface) {
                $blockedUsers[] = $user;
            }
            $io->progressAdvance();
        }
        $io->progressFinish();
        if (!$dryRun) {
            $em = $this->getEntityManager();
            $em->flush();
        }

        return $blockedUsers;
    }

    /**
     * @param SymfonyStyle $io
     * @param PersonInterface[] $blockedUsers
     */
    private function listBlocked(SymfonyStyle $io, $blockedUsers)
    {
        if (empty($blockedUsers)) {
            $io->note("No users were blocked");

            return;
        }

        $io->section("Blocked Users");
        $tableData = array_map(function (PersonInterface $user) {
            return [
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getSurname(),
                'email' => $user->getEmail(),
                'mobile' => $this->getPhoneUtil()->format($user->getMobile(), PhoneNumberFormat::E164),
                'cpf' => $user->getCpf(),
            ];
        }, $blockedUsers);
        $io->table(['First Name', 'Last Name', 'Email', 'Phone', 'CPF'], $tableData);
    }

    private function prepareAudit()
    {
        /** @var AuditConfiguration $auditConfig */
        $auditConfig = $this->getContainer()->get('simplethings_entityaudit.config');
        $auditConfig->setUsernameCallable(function () {
            $hostname = gethostname();

            return "# CLI: {$hostname}";
        });
    }

    /**
     * @return TwigSwiftMailer|object
     */
    private function getMailer()
    {
        return $this->getContainer()->get('lc.mailer');
    }

    /**
     * @param PersonInterface[] $blockedUsers
     */
    private function notifyUsers(SymfonyStyle $io, array $blockedUsers, $dryRun = true)
    {
        $io->section("Sending Emails");

        if ($dryRun) {
            $io->note("This is a DRY RUN. Emails won't be sent!");
        }

        $mailer = $this->getMailer();

        $io->progressStart(count($blockedUsers));
        foreach ($blockedUsers as $user) {
            if (!$user->getEmailConfirmedAt() instanceof \DateTime) {
                continue;
            }
            if (!$dryRun) {
                $mailer->sendAccountBlockedMessage($user);
            }
            $io->progressAdvance();
        }
        $io->progressFinish();
    }

    /**
     * @return UserManager|object
     */
    private function getUserManager()
    {
        return $this->getContainer()->get('lc.user_manager');
    }
}
