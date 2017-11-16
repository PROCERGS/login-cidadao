<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\AccountingBundle\Tests\Model;

use PROCERGS\LoginCidadao\AccountingBundle\Entity\ProcergsLink;
use PROCERGS\LoginCidadao\AccountingBundle\Model\AccountingReportEntry;
use PROCERGS\LoginCidadao\AccountingBundle\Model\GcsInterface;

class GcsInterfaceTest extends \PHPUnit_Framework_TestCase
{
    public function testGcsInterface()
    {
        $today = (new \DateTime())->format('dmY');
        $interfaceName = 'MY_INTERFACE';
        $start = \DateTime::createFromFormat('Y-m-d', '2017-01-01');
        $config = [];

        $clients = [
            (new AccountingReportEntry())
                ->setSystemType(ProcergsLink::TYPE_INTERNAL)
                ->setProcergsInitials(['XPTO'])
                ->setAccessTokens(111)
                ->setApiUsage(111)
                ->setProcergsOwner(['SOME_OWNER']),
            (new AccountingReportEntry())
                ->setSystemType(ProcergsLink::TYPE_INTERNAL)
                ->setProcergsInitials(['XPTO'])
                ->setAccessTokens(222)
                ->setApiUsage(222)
                ->setProcergsOwner(['SOME_OWNER']),
            (new AccountingReportEntry())
                ->setSystemType(ProcergsLink::TYPE_EXTERNAL)
                ->setAccessTokens(333)
                ->setApiUsage(333),
            (new AccountingReportEntry())
                ->setSystemType(ProcergsLink::TYPE_INTERNAL)
                ->setAccessTokens(444)
                ->setApiUsage(444),
            (new AccountingReportEntry())
                ->setSystemType('invalid')
                ->setAccessTokens(444)
                ->setApiUsage(444),
        ];

        $expectedBody = implode(PHP_EOL, [
            '2;SOME_OWNER;XPTO;'.(111 + 111 + 222 + 222),
            '2;EXTERNAL;EXTERNAL;'.(333 + 333),
            '2;;;'.(444 + 444),
            '2;;;'.(444 + 444),
        ]);

        $gcsInterface = new GcsInterface($interfaceName, $start, $config);
        foreach ($clients as $client) {
            $gcsInterface->addClient($client);
        }

        $header = "1;{$interfaceName};012017;{$today}";
        $tail = "9;4";

        $this->assertEquals($header, $gcsInterface->getHeader());
        $this->assertEquals($expectedBody, $gcsInterface->getBody());
        $this->assertEquals($tail, $gcsInterface->getTail());
        $this->assertEquals($header.PHP_EOL.$expectedBody.PHP_EOL.$tail, $gcsInterface->__toString());
    }

    public function testConfig()
    {
        $today = (new \DateTime())->format('dmY');
        $interfaceName = 'MY_INTERFACE';
        $start = \DateTime::createFromFormat('Y-m-d', '2017-01-01');
        $config = [
            'ignore_externals' => true,
        ];

        $clients = [
            (new AccountingReportEntry())
                ->setSystemType(ProcergsLink::TYPE_EXTERNAL)
                ->setAccessTokens(333)
                ->setApiUsage(333),
        ];

        $gcsInterface = new GcsInterface($interfaceName, $start, $config);
        foreach ($clients as $client) {
            $gcsInterface->addClient($client);
        }

        $header = "1;{$interfaceName};012017;{$today}";
        $body = '';
        $tail = "9;0";

        $this->assertEquals($header, $gcsInterface->getHeader());
        $this->assertEquals($body, $gcsInterface->getBody());
        $this->assertEquals($tail, $gcsInterface->getTail());
    }
}
