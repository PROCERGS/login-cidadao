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

use Doctrine\DBAL\Driver\Statement;
use LoginCidadao\OAuthBundle\Entity\ClientRepository;
use LoginCidadao\OAuthBundle\Model\ClientInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManager;
use LoginCidadao\OAuthBundle\Entity\Client;
use Symfony\Component\HttpFoundation\File\File;

class PopulateDatabaseCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('lc:database:populate')
            ->setDescription('Populates the database.')
            ->addArgument('dump_folder', InputArgument::REQUIRED, 'Where are the dumps?');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dir = realpath($input->getArgument('dump_folder'));
        $this->loadDumpFiles($dir, $output);
        $this->createDefaultOAuthClient($dir, $output);
    }

    private function loadDumpFiles($dir, OutputInterface $output)
    {
        $em = $this->getManager();
        $db = $em->getConnection();

        $db->beginTransaction();
        $countries = 0;
        $states = 0;
        $cities = 0;
        try {
            $db->exec('DELETE FROM city;');
            $db->exec('DELETE FROM state;');
            $db->exec('DELETE FROM country;');

            $countryInsert = 'INSERT INTO country (id, name, iso2, postal_format, postal_name, reviewed, iso3, iso_num) VALUES (:id, :name, :iso2, :postal_format, :postal_name, :reviewed, :iso3, :iso_num)';
            $countryQuery = $db->prepare($countryInsert);
            $countries = $this->loopInsert($dir, 'country_dump.csv', $countryQuery, [$this, 'prepareCountryData']);

            $statesInsert = 'INSERT INTO state (id, name, acronym, country_id, iso6, fips, stat, class, reviewed) VALUES (:id, :name, :acronym, :country_id, :iso6, :fips, :stat, :class, :reviewed)';
            $statesQuery = $db->prepare($statesInsert);
            $states = $this->loopInsert($dir, 'state_dump.csv', $statesQuery, [$this, 'prepareStateData']);

            $citiesInsert = 'INSERT INTO city (id, name, state_id, stat, reviewed) VALUES (:id, :name, :state_id, :stat, :reviewed)';
            $citiesQuery = $db->prepare($citiesInsert);
            $cities = $this->loopInsert($dir, 'city_dump.csv', $citiesQuery, [$this, 'prepareCityData']);

            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
        }
        $output->writeln("Added $countries countries, $states states and $cities cities.");
    }

    /**
     *
     * @return EntityManager
     */
    private function getManager()
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        return $em;
    }

    protected function prepareCountryData($row)
    {
        list($id, $name, $iso2, $postal_format, $postal_name, $reviewed, $iso3, $iso_num) = $row;
        $vars = compact('id', 'name', 'iso2', 'postal_format', 'postal_name', 'reviewed', 'iso3', 'iso_num');
        foreach ($vars as $k => $v) {
            if ($v === "") {
                $vars[$k] = null;
            }
        }

        return $vars;
    }

    protected function prepareStateData($row)
    {
        list($id, $name, $acronym, $country_id, $iso6, $fips, $stat, $class, $reviewed) = $row;

        return compact('id', 'name', 'acronym', 'country_id', 'iso6', 'fips', 'stat', 'class', 'reviewed');
    }

    protected function prepareCityData($row)
    {
        list($id, $name, $state_id, $stat, $reviewed) = $row;

        return compact('id', 'name', 'state_id', 'stat', 'reviewed');
    }

    /**
     * @param $dir
     * @param $fileName
     * @param Statement $query
     * @param $prepareFunction
     * @return int
     */
    private function loopInsert($dir, $fileName, $query, $prepareFunction)
    {
        $entries = 0;
        $file = $dir.DIRECTORY_SEPARATOR.$fileName;
        if (($handle = fopen($file, 'r')) !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                $data = $prepareFunction($row);
                $query->execute($data);
                $entries++;
            }
            fclose($handle);
        }

        return $entries;
    }

    protected function createDefaultOAuthClient($dir, OutputInterface $output)
    {
        if (!($this->getDefaultOAuthClient() instanceof Client)) {
            $uid = $this->getContainer()->getParameter('oauth_default_client.uid');
            $pictureName = 'client_logo.png';
            $picture = new File($dir.DIRECTORY_SEPARATOR.$pictureName);
            $domain = $this->getContainer()->getParameter('site_domain');
            $url = "//$domain";
            $grantTypes = [
                "authorization_code",
                "token",
                "password",
                "client_credentials",
                "refresh_token",
                "extensions",
            ];

            $clientManager = $this->getContainer()->get('fos_oauth_server.client_manager');
            /** @var ClientInterface $client */
            $client = $clientManager->createClient();
            $client->setName('Login Cidadão');
            $client->setDescription('Login Cidadão');
            $client->setSiteUrl($url);
            $client->setRedirectUris([$url]);
            $client->setAllowedGrantTypes($grantTypes);
            $client->setTermsOfUseUrl($url);
            $client->setPublished(true);
            $client->setVisible(false);
            $client->setUid($uid);
            $client->setImage($picture);
            $clientManager->updateClient($client);
            $output->writeln('Default Client created.');
        }
    }

    /**
     * @return ClientInterface
     */
    private function getDefaultOAuthClient()
    {
        /** @var ClientRepository $repo */
        $repo = $this->getManager()->getRepository('LoginCidadaoOAuthBundle:Client');
        $uid = $this->getContainer()->getParameter('oauth_default_client.uid');

        /** @var ClientInterface $client */
        $client = $repo->findOneBy(['uid' => $uid]);

        return $client;
    }
}
