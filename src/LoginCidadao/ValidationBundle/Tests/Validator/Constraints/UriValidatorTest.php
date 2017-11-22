<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\ValidationBundle\Tests\Validator\Constraints;

use LoginCidadao\ValidationBundle\Validator\Constraints\Uri;
use LoginCidadao\ValidationBundle\Validator\Constraints\UriValidator;
use Symfony\Bridge\PhpUnit\DnsMock;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest;
use Symfony\Component\Validator\Validation;

/**
 * @group dns-sensitive
 */
class UriValidatorTest extends AbstractConstraintValidatorTest
{
    protected function getApiVersion()
    {
        return Validation::API_VERSION_2_5;
    }

    protected function createValidator()
    {
        return new UriValidator();
    }

    public function testNullIsValid()
    {
        $this->validator->validate(null, new Uri());

        $this->assertNoViolation();
    }

    public function testEmptyStringIsValid()
    {
        $this->validator->validate('', new Uri());

        $this->assertNoViolation();
    }

    public function testEmptyStringFromObjectIsValid()
    {
        $this->validator->validate(new EmailProvider(), new Uri());

        $this->assertNoViolation();
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testExpectsStringCompatibleType()
    {
        $this->validator->validate(new \stdClass(), new Uri());
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     */
    public function testExpectsUriConstraint()
    {
        $this->validator->validate('', new Url());
    }

    /**
     * @dataProvider getValidUrls
     */
    public function testValidUrls($url)
    {
        $this->validator->validate($url, new Uri());

        $this->assertNoViolation();
    }

    public function getValidUrls()
    {
        return [
            ['http://a.pl'],
            ['http://www.google.com'],
            ['http://www.google.com.'],
            ['http://www.google.museum'],
            ['https://google.com/'],
            ['https://google.com:80/'],
            ['http://www.example.coop/'],
            ['http://www.test-example.com/'],
            ['http://www.symfony.com/'],
            ['http://symfony.fake/blog/'],
            ['http://symfony.com/?'],
            ['http://symfony.com/search?type=&q=url+validator'],
            ['http://symfony.com/#'],
            ['http://symfony.com/#?'],
            ['http://www.symfony.com/doc/current/book/validation.html#supported-constraints'],
            ['http://very.long.domain.name.com/'],
            ['http://localhost/'],
            ['http://myhost123/'],
            ['http://127.0.0.1/'],
            ['http://127.0.0.1:80/'],
            ['http://[::1]/'],
            ['http://[::1]:80/'],
            ['http://[1:2:3::4:5:6:7]/'],
            ['http://xn--sopaulo-xwa.com/'],
            ['http://xn--sopaulo-xwa.com.br/'],
            ['http://xn--e1afmkfd.xn--80akhbyknj4f/'],
            ['http://xn--mgbh0fb.xn--kgbechtv/'],
            ['http://xn--fsqu00a.xn--0zwm56d/'],
            ['http://xn--fsqu00a.xn--g6w251d/'],
            ['http://xn--r8jz45g.xn--zckzah/'],
            ['http://xn--mgbh0fb.xn--hgbk6aj7f53bba/'],
            ['http://xn--9n2bp8q.xn--9t4b11yi5a/'],
            ['http://xn--ogb.idn.icann.org/'],
            ['http://xn--e1afmkfd.xn--80akhbyknj4f.xn--e1afmkfd/'],
            ['http://xn--espaa-rta.xn--ca-ol-fsay5a/'],
            ['http://xn--d1abbgf6aiiy.xn--p1ai/'],
            ['http://username:password@symfony.com'],
            ['http://user-name@symfony.com'],
            ['http://symfony.com?'],
            ['http://symfony.com?query=1'],
            ['http://symfony.com/?query=1'],
            ['http://symfony.com#'],
            ['http://symfony.com#fragment'],
            ['http://symfony.com/#fragment'],
            ['http://symfony.com/#one_more%20test'],
            ['custom-scheme://symfony.com'],
        ];
    }

    /**
     * @dataProvider getInvalidUrls
     */
    public function testInvalidUrls($url)
    {
        $constraint = new Uri(['message' => 'myMessage']);

        $this->validator->validate($url, $constraint);

        $this->buildViolation('myMessage')
            ->setParameter('{{ value }}', '"'.$url.'"')
            ->setCode(Uri::INVALID_URL_ERROR)
            ->assertRaised();
    }

    public function getInvalidUrls()
    {
        return [
            ['google.com'],
            ['://google.com'],
            ['http ://google.com'],
            ['http:/google.com'],
            ['http://google.com::aa'],
            ['http://google.com:aa'],
            ['http://127.0.0.1:aa/'],
            ['http://[::1'],
            ['http://hello.☎/'],
            ['http://:password@@symfony.com'],
            ['http://username:passwordsymfony.com'],
            ['http://usern@me:password@symfony.com'],
            ['http://example.com/exploit.html?<script>alert(1);</script>'],
            ['http://example.com/exploit.html?hel lo'],
            ['http://example.com/exploit.html?not_a%hex'],
            ['invalid scheme://example.com/'],
            ['http://'],
        ];
    }

    /**
     * @dataProvider getValidCustomUrls
     */
    public function testCustomProtocolIsValid($url)
    {
        $constraint = new Uri();

        $this->validator->validate($url, $constraint);

        $this->assertNoViolation();
    }

    public function getValidCustomUrls()
    {
        return [
            ['ftp://google.com'],
            ['file://127.0.0.1'],
            ['git://[::1]/'],
        ];
    }

    /**
     * @dataProvider getCheckDns
     * @requires function Symfony\Bridge\PhpUnit\DnsMock::withMockedHosts
     */
    public function testCheckDns($violation)
    {
        DnsMock::withMockedHosts(['example.com' => [['type' => $violation ? '' : 'A']]]);

        $constraint = new Uri([
            'checkDNS' => true,
            'dnsMessage' => 'myMessage',
        ]);

        $this->validator->validate('http://example.com', $constraint);

        if (!$violation) {
            $this->assertNoViolation();
        } else {
            $this->buildViolation('myMessage')
                ->setParameter('{{ value }}', '"example.com"')
                ->setCode(Uri::INVALID_URL_ERROR)
                ->assertRaised();
        }
    }

    public function getCheckDns()
    {
        return [[true], [false]];
    }
}

class EmailProvider
{
    public function __toString()
    {
        return '';
    }
}
