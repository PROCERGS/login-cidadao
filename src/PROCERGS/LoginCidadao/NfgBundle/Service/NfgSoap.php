<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\NfgBundle\Service;


use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;
use PROCERGS\LoginCidadao\CoreBundle\Entity\NfgProfile;
use PROCERGS\LoginCidadao\NfgBundle\Exception\NfgServiceUnavailableException;
use PROCERGS\LoginCidadao\NfgBundle\Security\Credentials;
use Symfony\Component\DomCrawler\Crawler;

class NfgSoap implements NfgSoapInterface
{
    /** @var \SoapClient */
    private $client;

    /** @var Credentials */
    private $credentials;

    public function __construct(\SoapClient $client, Credentials $credentials)
    {
        $this->client = $client;
        $this->credentials = $credentials;
    }

    /**
     * @return string
     */
    public function getAccessID()
    {
        $response = $this->client->ObterAccessID($this->getAuthentication());

        if (false !== strpos($response->ObterAccessIDResult, ' ') || !isset($response->ObterAccessIDResult)) {
            throw new NfgServiceUnavailableException($response->ObterAccessIDResult);
        }

        return $response->ObterAccessIDResult;
    }

    public function getUserInfo($accessToken, $voterRegistration = null)
    {
        $params = $this->getAuthentication();
        $params['accessToken'] = $accessToken;
        if ($voterRegistration) {
            $params['voterRegistration'] = $voterRegistration;
        }

        $response = $this->client->ConsultaCadastro($params);
        $nfgProfile = new NfgProfile();
        $crawler = new Crawler($response->ConsultaCadastroResult);

        if ($crawler->filter('CodSitRetorno')->text() != 1) {
            throw new \RuntimeException($crawler->filter('MsgRetorno')->text());
        }
        $phoneNumber = $this->parsePhone($crawler);

        // Input example: 1970-01-01T00:00:00
        $birthday = \DateTime::createFromFormat('Y-m-d\TH:i:s', $this->getValue($crawler, 'DtNasc'));

        $nfgProfile
            ->setName($this->getValue($crawler, 'NomeConsumidor'))
            ->setEmail($this->getValue($crawler, 'EmailPrinc'))
            ->setBirthdate($birthday ?: null)
            ->setMobile($phoneNumber)
            ->setVoterRegistrationSit($this->getValue($crawler, 'CodSitTitulo'))
            ->setVoterRegistration($this->getValue($crawler, 'CodSitTitulo') != 0 ? $voterRegistration : null)
            ->setCpf($this->getValue($crawler, 'CodCpf'))
            ->setAccessLvl($this->getValue($crawler, 'CodNivelAcesso'));

        return $nfgProfile;
    }

    private function getAuthentication()
    {
        return [
            'organizacao' => $this->credentials->getOrganization(),
            'usuario' => $this->credentials->getUsername(),
            'senha' => $this->credentials->getPassword(),
        ];
    }

    private function parsePhone(Crawler $crawler)
    {
        if ($crawler->filter('NroFoneContato')->count() <= 0) {
            // Phone was not sent
            return null;
        }

        try {
            $phoneUtil = PhoneNumberUtil::getInstance();
            $phoneNumber = $phoneUtil->parse($crawler->filter('NroFoneContato')->text(), 'BR');
            $allowedTypes = [PhoneNumberType::MOBILE, PhoneNumberType::FIXED_LINE_OR_MOBILE];
            if (false === array_search($phoneUtil->getNumberType($phoneNumber), $allowedTypes)) {
                $phoneNumber = null;
            } else {
                $phoneNumber = $phoneUtil->format($phoneNumber, PhoneNumberFormat::E164);
            }
        } catch (NumberParseException $e) {
            $phoneNumber = null;
        }

        return $phoneNumber;
    }

    private function getValue(Crawler $crawler, $element)
    {
        $filter = $crawler->filter($element);
        if ($filter->count() <= 0) {
            return null;
        }

        return $filter->text();
    }
}
