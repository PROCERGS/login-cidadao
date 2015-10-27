<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManager;
use PROCERGS\LoginCidadao\NotificationBundle\Entity\Category;
use PROCERGS\OAuthBundle\Entity\Client;
use Symfony\Component\HttpFoundation\File\File;

class PopulateDatabaseCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('login-cidadao:database:populate')
            ->setDescription('Populates the database.')
            ->addArgument('dump_folder', InputArgument::REQUIRED, 'Where are the dumps?');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dir = realpath($input->getArgument('dump_folder'));
        $this->loadDumpFiles($dir, $output);
        $this->createDefaultOAuthClient($dir, $output);
        $this->createCategories($output);
    }

    private function loadDumpFiles($dir, OutputInterface $output)
    {
        $em = $this->getManager();
        $db = $em->getConnection();

        $db->beginTransaction();
        try {
            $db->exec('DELETE FROM city;');
            $db->exec('DELETE FROM state;');
            $db->exec('DELETE FROM country;');

            $countryInsert = 'INSERT INTO country (id, name, iso2, postal_format, postal_name, reviewed, iso3, iso_num) VALUES (:id, :name, :iso2, :postal_format, :postal_name, :reviewed, :iso3, :iso_num)';
            $countryQuery = $db->prepare($countryInsert);
            $countries = $this->loopInsert($dir, 'country_dump.csv', $countryQuery, array($this, 'prepareCountryData'));

            $statesInsert = 'INSERT INTO state (id, name, acronym, country_id, iso6, fips, stat, class, reviewed) VALUES (:id, :name, :acronym, :country_id, :iso6, :fips, :stat, :class, :reviewed)';
            $statesQuery = $db->prepare($statesInsert);
            $states = $this->loopInsert($dir, 'state_dump.csv', $statesQuery, array($this, 'prepareStateData'));

            $citiesInsert = 'INSERT INTO city (id, name, state_id, stat, reviewed) VALUES (:id, :name, :state_id, :stat, :reviewed)';
            $citiesQuery = $db->prepare($citiesInsert);
            $cities = $this->loopInsert($dir, 'city_dump.csv', $citiesQuery, array($this, 'prepareCityData'));

            $db->commit();
        } catch (Exception $e) {
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
        return $this->getContainer()->get('doctrine')->getManager();
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

    private function loopInsert($dir, $fileName, $query, $prepareFunction, $debug = false)
    {
        $entries = 0;
        $file = $dir . DIRECTORY_SEPARATOR . $fileName;
        if (($handle = fopen($file, 'r')) !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                $data = $prepareFunction($row);
                if ($debug) {
                    var_dump($data);
                }
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
            $picture = new File($dir . DIRECTORY_SEPARATOR . $pictureName);
            $domain = $this->getContainer()->getParameter('site_domain');
            $url = "//$domain";
            $grantTypes = array(
                "authorization_code",
                "token",
                "password",
                "client_credentials",
                "refresh_token",
                "extensions"
            );

            $clientManager = $this->getContainer()->get('fos_oauth_server.client_manager');
            $client = $clientManager->createClient();
            $client->setName('Login Cidadão');
            $client->setDescription('Login Cidadão');
            $client->setSiteUrl($url);
            $client->setRedirectUris(array($url));
            $client->setAllowedGrantTypes($grantTypes);
            $client->setTermsOfUseUrl($url);
            $client->setPublished(true);
            $client->setVisible(false);
            $client->setUid($uid);
            $client->setPictureFile($picture);
            $clientManager->updateClient($client);
            $output->writeln('Default Client created.');
        }
    }

    protected function createCategories(OutputInterface $output)
    {
        $em = $this->getManager();
        $categories = $em->getRepository('PROCERGSLoginCidadaoNotificationBundle:Category');

        $alertCategoryUid = $this->getContainer()->getParameter('notifications_categories_alert.uid');
        $alertCategory = $categories->findOneByUid($alertCategoryUid);

        if (!($alertCategory instanceof Category)) {
            $alertCategory = new Category();
            $alertCategory->setClient($this->getDefaultOAuthClient())
                ->setDefaultShortText('Alert')
                ->setDefaultTitle('Alert')
                ->setDefaultIcon('alert')
                ->setMarkdownTemplate('%shorttext%')
                ->setEmailable(false)
                ->setName('Alerts')
                ->setUid($alertCategoryUid);
            $em->persist($alertCategory);
            $em->flush();
            $output->writeln('Alert category created.');
        }
    }

    /**
     * @return Client
     */
    private function getDefaultOAuthClient()
    {
        $em = $this->getManager();
        $uid = $this->getContainer()->getParameter('oauth_default_client.uid');
        $client = $em->getRepository('PROCERGSOAuthBundle:Client')->findOneByUid($uid);

        return $client;
    }
}
