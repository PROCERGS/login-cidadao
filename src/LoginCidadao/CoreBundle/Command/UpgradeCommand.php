<?php

namespace LoginCidadao\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Doctrine\ORM\EntityManager;

class UpgradeCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('lc:upgrade')
            ->setDescription('Perform basic upgrade commands.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->clearMetadata($output);
        $this->cacheClear($output);
        //$this->installAssets($output);
    }

    /**
     *
     * @return EntityManager
     */
    private function getManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    private function clearMetadata(OutputInterface $output)
    {
        $output->writeln("Clearing Doctrine Metadata...");
        $command = $this->getApplication()->find('doctrine:cache:clear-metadata');

        $envs = $this->getEnvsInput();
        foreach ($envs as $env => $input) {
            $cmdOutput  = new BufferedOutput();
            $returnCode = $command->run($input, $cmdOutput);

            if ($returnCode === 0) {
                $out = explode("\n", trim($cmdOutput->fetch()));
                $output->writeln("[$env]\t- ".end($out));
            }
        }
    }

    private function installAssets(OutputInterface $output)
    {
        $output->write("Installing assets...\t");
        $input    = $this->getEnvsInput()['prod'];
        $commands = array('assets:install', 'assetic:dump');
        foreach ($commands as $command) {
            $cmdOutput  = new BufferedOutput();
            $returnCode = $this->getApplication()
                    ->find($command)->run($input, $cmdOutput);

            if ($returnCode !== 0) {
                $output->writeln("[FAIL]");
                $output->writeln("$command failed. Run it separately to find out why.");
                return;
            }
        }
        $output->writeln("[DONE]");
    }

    private function cacheClear(OutputInterface $output)
    {
        $output->write("Clearing cache...\t");
        $input   = new ArrayInput(array('--env' => 'dev', '--no-warmup'));
        $command = $this->getApplication()->find('cache:clear');

        $cmdOutput  = new BufferedOutput();
        $returnCode = $command->run($input, $cmdOutput);

        if ($returnCode !== 0) {
            $output->writeln("[FAIL]");
            $output->writeln("cache:clear command failed. You may need to manually delete the cache folders.");
            return;
        }

        $output->writeln("[DONE]");
        return;

        $envs = $this->getEnvsInput(array('--no-warmup'));
        foreach ($envs as $env => $input) {
            $cmdOutput = new BufferedOutput();
            try {
                $returnCode = $command->run($input, $cmdOutput);
            } catch (\Exception $e) {
                $returnCode = false;
            }

            if ($returnCode !== 0) {
                $output->writeln("[FAIL]");
                $output->writeln("cache:clear command failed. You may need to manually delete the cache folders.");
                return;
            }
            $output->writeln("$env done");
        }
        $output->writeln("[DONE]");
    }

    private function getEnvsInput($custom = array())
    {
        return array(
            'prod' => new ArrayInput(array_merge(array('--env' => 'prod'),
                    $custom)),
            'env' => new ArrayInput(array_merge(array('--env' => 'dev'), $custom))
        );
    }
}
