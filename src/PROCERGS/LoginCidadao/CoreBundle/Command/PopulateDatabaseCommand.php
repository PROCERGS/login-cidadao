<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManager;
use PROCERGS\LoginCidadao\CoreBundle\Entity\Notification\Category;

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
        //$this->loadDumpFiles($dir, $output);
        $this->createCategories($output);
    }

    private function loadDumpFiles($dir, OutputInterface $output)
    {
        $em = $this->getManager();
        $db = $em->getConnection();

        $db->beginTransaction();
        try {
            $db->exec('DELETE FROM city;');
            $db->exec('DELETE FROM uf;');
            $db->exec('DELETE FROM country;');

            $countryInsert = 'INSERT INTO country (id, name, iso2, postal_format, postal_name, reviewed, iso3, iso_num) VALUES (:id, :name, :iso2, :postal_format, :postal_name, :reviewed, :iso3, :iso_num)';
            $countryQuery = $db->prepare($countryInsert);
            $countries = $this->loopInsert($dir, 'country_dump.csv', $countryQuery, array($this, 'prepareCountryData'));

            $statesInsert = 'INSERT INTO uf (id, name, acronym, country_id, iso6, fips, stat, class, reviewed) VALUES (:id, :name, :acronym, :country_id, :iso6, :fips, :stat, :class, :reviewed)';
            $statesQuery = $db->prepare($statesInsert);
            $states = $this->loopInsert($dir, 'uf_dump.csv', $statesQuery, array($this, 'prepareStateData'));

            $citiesInsert = 'INSERT INTO city (id, name, uf_id, stat, reviewed) VALUES (:id, :name, :uf_id, :stat, :reviewed)';
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
        return compact('id', 'name', 'iso2', 'postal_format', 'postal_name', 'reviewed', 'iso3', 'iso_num');
    }

    protected function prepareStateData($row)
    {
        list($id, $name, $acronym, $country_id, $iso6, $fips, $stat, $class, $reviewed) = $row;
        return compact('id', 'name', 'acronym', 'country_id', 'iso6', 'fips', 'stat', 'class', 'reviewed');
    }

    protected function prepareCityData($row)
    {
        list($id, $name, $uf_id, $stat, $reviewed) = $row;
        return compact('id', 'name', 'uf_id', 'stat', 'reviewed');
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

    protected function createCategories(OutputInterface $output)
    {
        $em = $this->getManager();
        $categories = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:Notification\Category');
        
        $count = $em->createQuery("SELECT c FROM PROCERGSLoginCidadaoCoreBundle:Notification\Category c");
        $count->setMaxResults(1)->execute();
        $result = $count->getResult();
        
        if (count($result) === 0) {
            // Create categories
        }
        
        $output->writeln(count($result) > 0 ? 'Has categories' : 'Nothing here');
    }

}
