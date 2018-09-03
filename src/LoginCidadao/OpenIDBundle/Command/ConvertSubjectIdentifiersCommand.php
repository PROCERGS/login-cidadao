<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\OpenIDBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use LoginCidadao\CoreBundle\Entity\Authorization;
use LoginCidadao\CoreBundle\Entity\AuthorizationRepository;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;
use LoginCidadao\OpenIDBundle\Manager\ClientManager;
use LoginCidadao\OpenIDBundle\Service\SubjectIdentifierService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @codeCoverageIgnore Ignoring command code. Ideally it should be refactored into a testable Service
 */
class ConvertSubjectIdentifiersCommand extends ContainerAwareCommand
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var array */
    private $clientMetadata = [];

    protected function configure()
    {
        $this
            ->setName('lc:oidc:convert-subject-identifiers')
            ->setDescription("Convert Subject Identifiers, from public to pairwise.")
            ->addArgument('client', InputArgument::REQUIRED, "Client's ID")
            ->addArgument('file', InputArgument::REQUIRED, "File where to save the conversion result")
            ->addOption('dry-run', null, InputOption::VALUE_NONE,
                'Prevent changes from being persisted on the database');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dryRyn = $input->getOption('dry-run');
        $io = new SymfonyStyle($input, $output);

        $io->title('Convert Public Subject Identifiers to Pairwise');

        $fp = $this->checkAndOpenFile($io, $input);
        if ($fp === false) {
            return;
        }

        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $client = $this->getClient($input->getArgument('client'));

        $count = $this->checkCount($io, $client);
        if ($count === null) {
            fclose($fp);

            return;
        }

        $publicSubs = $this->getPublicSubQuery($client)
            ->getQuery()->iterate();

        $io->section("Generating Sector Identifiers...");

        /** @var SubjectIdentifierService $subIdService */
        $subIdService = $this->getContainer()->get('oidc.subject_identifier.service');

        $metadata = $this->getClientMetadata($client);
        $metadata->setSubjectType('pairwise');

        $io->progressStart($count);
        $this->em->beginTransaction();
        $done = 0;
        try {
            foreach ($publicSubs as $row) {
                /** @var Authorization $auth */
                $auth = $row[0];
                $person = $auth->getPerson();

                $sub = $subIdService->convertSubjectIdentifier($person, $metadata);

                $row = [$person->getId(), $sub->getSubjectIdentifier()];
                if (false === fputcsv($fp, $row)) {
                    throw new \RuntimeException('Error writing CSV to file! Aborting!');
                }

                $this->em->persist($sub);
                if (!$dryRyn && $done++ % 200 === 0) {
                    $this->em->commit();
                    $this->em->flush();
                    $this->em->clear();
                    $this->em->beginTransaction();
                }
                $io->progressAdvance();
            }
            $io->progressFinish();

            if ($dryRyn) {
                $this->em->rollback();
                $io->note("Dry Run: no changes were persisted.");
            } else {
                $this->em->commit();
                $this->em->flush();
                $this->em->clear();
            }

            $io->success("Done! {$done} Authorizations updated!");
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            $this->em->rollback();
            $this->em->clear();
        }
        fclose($fp);
    }

    /**
     * @return AuthorizationRepository
     */
    private function getAuthorizationRepository()
    {
        /** @var AuthorizationRepository $repo */
        $repo = $this->em->getRepository('LoginCidadaoCoreBundle:Authorization');

        return $repo;
    }

    /**
     * @param ClientInterface $client
     * @return ClientMetadata
     */
    private function getClientMetadata(ClientInterface $client)
    {
        $id = $client->getId();
        if (array_key_exists($id, $this->clientMetadata)) {
            $metadata = $this->clientMetadata[$id];
        } else {
            $metadata = $client->getMetadata();
            $this->clientMetadata[$id] = $metadata;
        }

        return $metadata;
    }

    /**
     * @param $clientId
     * @return ClientInterface
     */
    private function getClient($clientId)
    {
        /** @var ClientManager $clientManager */
        $clientManager = $this->getContainer()->get('lc.client_manager');

        $client = $clientManager->getClientById($clientId);
        if (!$client instanceof ClientInterface) {
            // TODO: use appropriate exception
            throw new \RuntimeException('Client not found');
        }

        return $client;
    }

    /**
     * @param ClientInterface $client
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getPublicSubQuery(ClientInterface $client)
    {
        $repo = $this->getAuthorizationRepository();

        return $repo->createQueryBuilder('a')
            ->innerJoin('LoginCidadaoCoreBundle:Person', 'p', 'WITH', 'a.person = p')
            ->leftJoin(
                'LoginCidadaoOpenIDBundle:SubjectIdentifier',
                's',
                'WITH',
                's.person = a.person AND s.client = a.client'
            )
            ->where('CAST(p.id AS text) = s.subjectIdentifier')
            ->andWhere('a.client = :client')
            ->setParameter('client', $client);
    }

    /**
     * @param SymfonyStyle $io
     * @param ClientInterface $client
     * @return int|null
     */
    private function checkCount(SymfonyStyle $io, ClientInterface $client)
    {
        try {
            $count = $this->getPublicSubQuery($client)
                ->select('COUNT(a)')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException $e) {
            $count = 0;
        } catch (NonUniqueResultException $e) {
            $io->error("Error counting public subject identifiers: {$e->getMessage()}");

            return null;
        }

        if ($count === 0) {
            $io->success("No changes needed. You're all set!");

            return null;
        }

        return $count;
    }

    /**
     * @param SymfonyStyle $io
     * @param InputInterface $input
     * @return false|resource file pointer on success or false on error
     */
    private function checkAndOpenFile(SymfonyStyle $io, InputInterface $input)
    {
        $file = $input->getArgument('file');
        if (file_exists($file) && !$io->confirm("File '{$file}' already exists. Override?", false)) {
            $io->comment('Aborted');

            return false;
        }
        if (false === $fp = fopen($file, 'w')) {
            $io->error("Error opening file '{$file}'...");
        }

        return $fp;
    }
}
