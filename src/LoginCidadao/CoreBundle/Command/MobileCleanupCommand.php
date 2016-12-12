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
use LoginCidadao\ValidationBundle\Validator\Constraints\MobilePhoneNumberValidator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManager;

class MobileCleanupCommand extends ContainerAwareCommand
{
    const INVALID_RECOVERED = 'invalid_recovered';
    const IRRECOVERABLE = 'irrecoverable';
    const VALID = 'valid';
    const VALID_ADDED_9 = 'valid_added_9';

    const REGEX_COUNTRY = '(\+?0|\+?55)?';
    const REGEX_AREA_CODE = '(1[1-9]|2[12478]|3[1-5]|3[7-8]|4[1-9]|5[1345]|6[1-9]|7[134579]|8[1-9]|9[1-9])';
    const REGEX_SUBSCRIBER = '((?:9[6789]|[6789])\d{7})';

    /** @var PhoneNumberUtil */
    private $phoneUtil;

    /** @var array */
    private $fileHandles = [];

    /** @var array */
    private $counts = [];

    private $recovered = 0;

    protected function configure()
    {
        $this
            ->setName('lc:mobile-cleanup')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'File to where decisions should be logged.'
            )
            ->setDescription('Erases mobile numbers that do not comply with E.164 in any recoverable way.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        $this->phoneUtil = PhoneNumberUtil::getInstance();

        $this->fileHandles['log'] = fopen($file, 'w+');

        $this->counts = [
            self::INVALID_RECOVERED => 0,
            self::IRRECOVERABLE => 0,
            self::VALID => 0,
            self::VALID_ADDED_9 => 0,
        ];

        $this->processPhones($input, $output);
    }

    /**
     *
     * @return EntityManager
     */
    private function getManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    private function processPhones(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');

        $io = new SymfonyStyle($input, $output);

        $io->title('Mobile Numbers Cleanup');

        $progress = new ProgressIndicator($output);
        $progress->start('Scanning phones...');

        $results = $this->getQuery()->iterate();
        while (true) {
            $progress->setMessage($this->getProgressMessage());
            $row = $this->getNextEntry($results);
            if (false === $row) {
                break;
            }
            if (null === $row) {
                continue;
            }

            /** @var PhoneNumber $mobile */
            $mobile = $row['mobile'];
            if (false === MobilePhoneNumberValidator::isMobile($mobile)) {
                $added9 = $this->tryToFix($mobile);
                if (false === $added9) {
                    $this->write(self::IRRECOVERABLE, $mobile, $row['id']);
                } else {
                    $this->write(self::VALID_ADDED_9, $mobile, $row['id'], $added9);
                }
            } else {
                $this->write(self::VALID, $mobile, $row['id']);
            }
        }
        $progress->finish($this->getProgressMessage());
        $io->newLine();

        foreach ($this->fileHandles as $handle) {
            fclose($handle);
        }

        $io->success("Results saved to {$file}");
    }

    private function getNextEntry(IterableResult $results)
    {
        try {
            $next = $results->next();

            if (false === $next) {
                return false;
            }

            return reset($next);
        } catch (ConversionException $e) {
            preg_match('/(\+?\d+)/', $e->getMessage(), $m);
            $phone = $this->isRecoverable($m[0]);
            if ($phone instanceof PhoneNumber) {
                $this->write(self::INVALID_RECOVERED, $m[0], null, $phone);
            } else {
                $this->write(self::IRRECOVERABLE, $m[0]);
            }

            return null;
        }
    }

    private function getProgressMessage()
    {
        $counts = $this->counts;

        return sprintf(
            '%s valid unchanged || %s valid added 9 || %s invalid recovered || %s irrecoverable',
            $counts[self::VALID],
            $counts[self::VALID_ADDED_9],
            $counts[self::INVALID_RECOVERED],
            $counts[self::IRRECOVERABLE]
        );
    }

    private function tryToFix(PhoneNumber $phone)
    {
        $regexAreaCode = self::REGEX_AREA_CODE;
        $regexSubscriber = self::REGEX_SUBSCRIBER;
        $detectMobileRegex = "/^{$regexAreaCode}{$regexSubscriber}$/";

        if ($phone->getCountryCode() != 55 || preg_match($detectMobileRegex, $phone->getNationalNumber(), $m) !== 1) {
            return false;
        }

        $result = new PhoneNumber();
        $result->setCountryCode($phone->getCountryCode());
        $result->setNationalNumber(sprintf('%s9%s', $m[1], $m[2]));

        if (false === MobilePhoneNumberValidator::isMobile($result)) {
            return false;
        }

        return $this->phoneUtil->format($result, PhoneNumberFormat::E164);
    }

    private function tryToRecover($phone, $recoveredHandle)
    {
        $result = null;

        // Replace 0 by +55
        $regex0to55 = '/^[+]?0(1[1-9]|2[12478]|3[1-5]|3[7-8]|4[1-9]|5[1345]|6[1-9]|7[134579]|8[1-9]|9[1-9])([0-9]{8,9})$/';
        if (preg_match($regex0to55, $phone, $m)) {
            $result = "+55{$m[1]}{$m[2]}";
        }

        // Missing +55
        $missing55 = '/^(1[1-9]|2[12478]|3[1-5]|3[7-8]|4[1-9]|5[1345]|6[1-9]|7[134579]|8[1-9]|9[1-9])([0-9]{8,9})$/';
        if (preg_match($missing55, $phone, $m)) {
            $result = "+55{$m[1]}{$m[2]}";
        }

        if (null !== $result) {
            fputcsv($recoveredHandle, [$phone, $result]);
            $this->recovered++;
        }

        return $result;
    }

    private function isRecoverable($phone)
    {
        $phoneE164 = $this->phoneNumberToString($phone);

        $regexCountry = self::REGEX_COUNTRY;
        $regexAreaCode = self::REGEX_AREA_CODE;
        $regexSubscriber = self::REGEX_SUBSCRIBER;

        $regex = "/^{$regexCountry}{$regexAreaCode}{$regexSubscriber}$/";

        if (preg_match($regex, $phoneE164, $m) !== 1) {
            return false;
        }

        $country = $m[1];
        $area = $m[2];
        $subscriber = $m[3];

        if ($country == '0' || $country == '+0' || $country == '+55') {
            $country = '55';
        }

        if (strlen($subscriber) == 8) {
            $subscriber = '9'.$subscriber;
        }

        $phone = new PhoneNumber();
        $phone->setCountryCode($country);
        $phone->setNationalNumber($area.$subscriber);

        return $phone;
    }

    private function getQuery()
    {
        /** @var PersonRepository $repo */
        $repo = $this->getManager()->getRepository('LoginCidadaoCoreBundle:Person');

        $query = $repo->createQueryBuilder('p')
            ->select('p.id, p.mobile')
            ->where('p.mobile IS NOT NULL')
            ->andWhere("p.mobile != ''")
            ->getQuery();

        return $query;
    }

    /**
     * @param $situation
     * @param string $original
     * @param int $id
     * @param string $new
     */
    private function write($situation, $original, $id = null, $new = null)
    {
        $data = [
            $situation,
            $id,
            $this->phoneNumberToString($original),
            $this->phoneNumberToString($new),
        ];
        fputcsv($this->fileHandles['log'], $data);
        $this->counts[$situation]++;
    }

    private function phoneNumberToString($phone)
    {
        if ($phone instanceof PhoneNumber) {
            return $this->phoneUtil->format($phone, PhoneNumberFormat::E164);
        } else {
            return $phone;
        }
    }
}
