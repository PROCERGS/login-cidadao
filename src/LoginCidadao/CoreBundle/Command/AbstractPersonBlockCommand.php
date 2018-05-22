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
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use LoginCidadao\CoreBundle\Mailer\TwigSwiftMailer;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\CoreBundle\Security\User\Manager\UserManager;
use SimpleThings\EntityAudit\AuditConfiguration;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class AbstractPersonBlockCommand
 * @package LoginCidadao\CoreBundle\Command
 * @codeCoverageIgnore
 */
abstract class AbstractPersonBlockCommand extends ContainerAwareCommand
{
    /** @var PhoneNumberUtil|null */
    private $phoneUtil;

    /**
     * @param SymfonyStyle $io
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return PersonInterface[]
     */
    abstract protected function getUsers(SymfonyStyle $io, InputInterface $input, OutputInterface $output);

    protected function configure()
    {
        $this->addOption(
            'dry-run',
            null,
            InputOption::VALUE_NONE,
            'Do NOT persist changes.');
    }

    /**
     * @return EntityManagerInterface|object
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * @return UserManager|object
     */
    protected function getUserManager()
    {
        return $this->getContainer()->get('lc.user_manager');
    }

    protected function getPhoneUtil()
    {
        if (!$this->phoneUtil instanceof PhoneNumberUtil) {
            $this->phoneUtil = PhoneNumberUtil::getInstance();
        }

        return $this->phoneUtil;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $users = $this->getUsers($io, $input, $output);

        if (empty($users)) {
            $io->note("No users found...");

            return;
        }
        $dryRun = $input->getOption('dry-run');

        $this->prepareAudit();

        $blockedUsers = $this->blockUsers($io, $users, $dryRun);
        $this->listBlocked($io, $blockedUsers);

        if (!empty($blockedUsers)) {
            $this->notifyUsers($io, $blockedUsers, $dryRun);
        }
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
     * @param SymfonyStyle $io
     * @param PersonInterface[] $blockedUsers
     * @param bool $dryRun
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
            if ($dryRun || !$user->getEmailConfirmedAt() instanceof \DateTime) {
                continue;
            }
            $mailer->sendAccountBlockedMessage($user);
            $io->progressAdvance();
        }
        $io->progressFinish();
    }
}
