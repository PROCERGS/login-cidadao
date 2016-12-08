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

use Doctrine\DBAL\Types\ConversionException;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use libphonenumber\NumberFormat;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;
use LoginCidadao\CoreBundle\Entity\Person;
use LoginCidadao\CoreBundle\Entity\PersonRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManager;

class BatchUpdateMobileCommand extends ContainerAwareCommand
{
    private $skipped = 0;
    private $updated = 0;

    protected function configure()
    {
        $this
            ->setName('lc:update-mobile')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'CSV containing phones to be updated in the format status,id,old mobile,new mobile.'
            )
            ->addOption(
                'output',
                'o',
                InputOption::VALUE_REQUIRED,
                'File where the SQL will be saved'
            )
            ->setDescription('Erases mobile numbers that do not comply with E.164 in any recoverable way.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title("Batch Updating Mobiles");

        $progress = new ProgressIndicator($output);
        $progress->start('Preparing to update phones...');

        $inHandle = fopen($input->getArgument('file'), 'r');
        $outHandle = fopen($input->getOption('output'), 'w+');

        fputs($outHandle, 'BEGIN;'.PHP_EOL);
        while (false !== ($row = fgetcsv($inHandle))) {
            $progress->setMessage($this->getProgressMessage());

            $status = $row[0];
            $id = $row[1];
            $old = $row[2];
            $new = $row[3];

            if (strcmp($old, $new) === 0
                || strcmp($status, 'valid') === 0
            ) {
                $this->skipped++;
                continue;
            }

            $new = strlen($new) > 0 ? $new : null;

            fputs($outHandle, $this->getUpdateSql($old, $id, $new).PHP_EOL);
            $this->updated++;

            continue;
        }
        fputs($outHandle, 'COMMIT;'.PHP_EOL);
        fclose($outHandle);
        fclose($inHandle);

        $progress->finish($this->getProgressMessage());

        $io->success("SQL UPDATE queries generated at {$input->getOption('output')}.");
    }

    private function getProgressMessage()
    {
        return sprintf(
            '%s skipped || %s updated',
            $this->skipped,
            $this->updated
        );
    }

    private function getUpdateSql($old, $id = null, $new = null)
    {
        $old = "'{$old}'";
        $new = $new === null ? 'NULL' : "'{$new}'";

        if ($id) {
            $where = "id = {$id}";
        } else {
            $where = "mobile = {$old}";
        }

        return "UPDATE person SET updatedat = NOW(), mobile = {$new} WHERE {$where};";
    }
}
