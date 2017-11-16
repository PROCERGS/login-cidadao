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
use LoginCidadao\CoreBundle\Entity\Authorization;
use LoginCidadao\CoreBundle\Entity\AuthorizationRepository;
use LoginCidadao\OAuthBundle\Entity\Client;
use LoginCidadao\OpenIDBundle\Entity\ClientMetadata;
use LoginCidadao\OpenIDBundle\Entity\SubjectIdentifier;
use LoginCidadao\OpenIDBundle\Service\SubjectIdentifierService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateSubjectIdentifiersCommand extends ContainerAwareCommand
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var array */
    private $clientMetadata = [];

    protected function configure()
    {
        $this
            ->setName('lc:oidc:generate-subject-identifiers')
            ->setDescription("Generates and persists Subject Identifiers for Authorizations that don't have it.")
            ->setHelp(
                "This command will persist the Subject Identifiers for all Authorizations that do not have it yet since it's a new feature."
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Generate Missing Subject Identifiers');

        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $io->section("Searching Authorizations with missing Subject Identifiers...");

        $repo = $this->getAuthorizationRepository();
        $count = $repo->createQueryBuilder('a')
            ->select('COUNT(a)')
            ->leftJoin(
                'LoginCidadaoOpenIDBundle:SubjectIdentifier',
                's',
                'WITH',
                's.person = a.person AND s.client = a.client'
            )
            ->where('s.subjectIdentifier IS NULL')
            ->getQuery()
            ->getSingleScalarResult();

        if ($count === 0) {
            $io->success("No changes needed. You're all set!");

            return;
        }

        $io->text("{$count} entries found");

        $missingSubId = $repo->createQueryBuilder('a')
            ->leftJoin(
                'LoginCidadaoOpenIDBundle:SubjectIdentifier',
                's',
                'WITH',
                's.person = a.person AND s.client = a.client'
            )
            ->where('s.subjectIdentifier IS NULL')
            ->getQuery()->iterate();

        $io->section("Generating Sector Identifiers...");

        /** @var SubjectIdentifierService $subIdService */
        $subIdService = $this->getContainer()->get('oidc.subject_identifier.service');

        $io->progressStart($count);
        $current = 0;
        foreach ($missingSubId as $row) {
            /** @var Authorization $auth */
            $auth = $row[0];
            $subId = $subIdService->getSubjectIdentifier(
                $auth->getPerson(),
                $this->getClientMetadata($auth->getClient()),
                false
            );
            $sub = new SubjectIdentifier();
            $sub
                ->setPerson($auth->getPerson())
                ->setClient($auth->getClient())
                ->setSubjectIdentifier($subId);
            $this->em->persist($sub);
            if ($current++ % 50 === 0) {
                $this->em->flush();
                $this->em->clear();
            }
            $io->progressAdvance();
        }
        $this->em->flush();
        $this->em->clear();
        $io->progressFinish();

        $io->success("Done! {$count} Authorizations updated!");
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
     * @param Client $client
     * @return ClientMetadata
     */
    private function getClientMetadata(Client $client)
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
}
