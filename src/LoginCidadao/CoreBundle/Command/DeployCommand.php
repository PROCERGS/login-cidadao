<?php

namespace LoginCidadao\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Style\SymfonyStyle;

class DeployCommand extends ContainerAwareCommand
{
    private $updateDb = false;

    protected function configure()
    {
        $this
            ->setName('lc:deploy')
            ->addOption('--update-db', null, InputOption::VALUE_NONE,
                'Update database schema without prompting')
            ->setDescription('Perform basic deploy commands.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->updateDb = $input->getOption('update-db');

        $io = new SymfonyStyle($input, $output);
        $io->title("Running deploy tasks...");

        $this->clearMetadata($io);
        $this->clearCache($io, 'prod');
        $this->checkDatabase($io);
        $this->installAssets($io);
    }

    private function clearMetadata(SymfonyStyle $io)
    {
        $io->section("Clearing Doctrine Metadata...");
        $command = $this->getApplication()->find('doctrine:cache:clear-metadata');

        $envs = $this->getEnvsInput();
        $io->progressStart(count($envs));
        foreach ($envs as $env => $input) {
            $cmdOutput = new BufferedOutput();
            $returnCode = $command->run($input, $cmdOutput);

            if ($returnCode !== 0) {
                $io->newLine(2);
                $io->error("Couldn't clear metadata cache on $env");

                return;
            }
            $io->progressAdvance();
        }
        $io->progressFinish();
    }

    private function installAssets(SymfonyStyle $io)
    {
        $io->section("Installing assets...");
        $input = $this->getEnvsInput('prod');
        $commands = ['assets:install', 'assetic:dump'];
        $io->progressStart(count($commands));
        foreach ($commands as $command) {
            $cmdOutput = new BufferedOutput();
            $returnCode = $this->getApplication()
                ->find($command)->run($input, $cmdOutput);

            if ($returnCode !== 0) {
                $io->newLine(2);
                $io->error("$command failed. Run it separately to find out why.");

                return;
            }
            $io->progressAdvance();
        }
        $io->progressFinish();
    }

    private function clearCache(SymfonyStyle $io, $env)
    {
        $io->section("Clearing cache ($env)...");
        $io->progressStart(1);
        $input = $this->getEnvsInput($env);
        $command = $this->getApplication()->find('cache:clear');

        $cmdOutput = new BufferedOutput();
        $returnCode = $command->run($input, $cmdOutput);

        if ($returnCode !== 0) {
            $io->error("cache:clear command failed. You may need to manually delete the cache folders.");

            return;
        }

        $io->progressFinish();
    }

    private function getEnvsInput($env = null)
    {
        $envs = [
            'prod' => new ArrayInput(['--env' => 'prod']),
            'dev' => new ArrayInput(['--env' => 'dev']),
        ];

        if ($env === null) {
            return $envs;
        } else {
            return $envs[$env];
        }
    }

    private function checkDatabase(SymfonyStyle $io)
    {
        $io->section("Checking database schema...");

        $defaultEm = $this->checkSchemaNeedsUpdate('default');
        $logsEm = $this->checkSchemaNeedsUpdate('logs');

        if (!$defaultEm && !$logsEm) {
            $io->success(trim($defaultEm));

            return;
        }

        if ($defaultEm) {
            $this->updateSchema($io, explode("\n", trim($defaultEm)), 'default');
        }
        if ($logsEm) {
            $this->updateSchema($io, explode("\n", trim($logsEm)), 'logs');
        }
    }

    private function checkSchemaNeedsUpdate($entityManager)
    {
        $cmdOutput = new BufferedOutput();
        $command = $this->getApplication()->find('doctrine:schema:update');
        $input = new ArrayInput([
            '--env' => 'dev',
            '--dump-sql' => true,
            '--em' => $entityManager,
        ]);

        $command->run($input, $cmdOutput);

        $output = $cmdOutput->fetch();
        if (strstr($output, 'Nothing to update') !== false) {
            return false;
        }

        return $output;
    }

    private function updateSchema(SymfonyStyle $io, $queries, $entityManager)
    {
        $io->caution("Your database schema needs to be updated. The following queries will be run:");
        $io->listing($queries);
        if ($this->updateDb === false &&
            $io->confirm("Should we run this queries now?", false) === false) {
            return;
        }

        $cmdOutput = new BufferedOutput();
        $command = $this->getApplication()->find('doctrine:schema:update');
        $force = new ArrayInput([
            '--env' => 'dev',
            '--dump-sql' => true,
            '--force' => true,
            '--em' => $entityManager,
        ]);
        $command->run($force, $cmdOutput);

        $result = $cmdOutput->fetch();
        if (strstr($result, 'Database schema updated successfully!') === false) {
            $io->error("Couldn't update the schema. Run 'doctrine:schema:update' separately to find out why");
        }
        $io->success("Database schema updated successfully!");

        $this->clearMetadata($io);
    }
}
