<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new FOS\OAuthServerBundle\FOSOAuthServerBundle(),
            new LoginCidadao\OAuthBundle\LoginCidadaoOAuthBundle(),
            new LoginCidadao\CoreBundle\LoginCidadaoCoreBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new Bmatzner\FontAwesomeBundle\BmatznerFontAwesomeBundle(),

            new SimpleThings\EntityAudit\SimpleThingsEntityAuditBundle(),
            new HWI\Bundle\OAuthBundle\HWIOAuthBundle(),

            new LoginCidadao\LocaleBundle\LoginCidadaoLocaleBundle(),
            new LoginCidadao\APIBundle\LoginCidadaoAPIBundle(),
            new LoginCidadao\ValidationBundle\LoginCidadaoValidationBundle(),
            new Beelab\Recaptcha2Bundle\BeelabRecaptcha2Bundle(),

            new Knp\Bundle\GaufretteBundle\KnpGaufretteBundle(),
            new Vich\UploaderBundle\VichUploaderBundle(),

            new JMS\DiExtraBundle\JMSDiExtraBundle($this),
            new JMS\AopBundle\JMSAopBundle(),
            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
            new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
            
            new LoginCidadao\BadgesControlBundle\LoginCidadaoBadgesControlBundle(),
            new LoginCidadao\BadgesBundle\LoginCidadaoBadgesBundle(),
            
            new Scheb\TwoFactorBundle\SchebTwoFactorBundle(),
            new LoginCidadao\ValidationControlBundle\LoginCidadaoValidationControlBundle(),

            new LoginCidadao\TOSBundle\LoginCidadaoTOSBundle(),

            new OAuth2\ServerBundle\OAuth2ServerBundle(),
            new LoginCidadao\OpenIDBundle\LoginCidadaoOpenIDBundle(),
            new LoginCidadao\StatsBundle\LoginCidadaoStatsBundle(),

            new Nelmio\CorsBundle\NelmioCorsBundle(),
            new Nelmio\SecurityBundle\NelmioSecurityBundle(),
            new Liip\MonitorBundle\LiipMonitorBundle(),

            new Donato\PathWellBundle\DonatoPathWellBundle(),
            new Misd\PhoneNumberBundle\MisdPhoneNumberBundle(),
            new Rollerworks\Bundle\PasswordStrengthBundle\RollerworksPasswordStrengthBundle(),
            new LoginCidadao\PhoneVerificationBundle\LoginCidadaoPhoneVerificationBundle(),
            new LoginCidadao\TaskStackBundle\LoginCidadaoTaskStackBundle(),
            new LoginCidadao\DynamicFormBundle\LoginCidadaoDynamicFormBundle(),
            new LoginCidadao\LogBundle\LoginCidadaoLogBundle(),

            // PROCERGS
            new PROCERGS\LoginCidadao\CoreBundle\PROCERGSLoginCidadaoCoreBundle(),
            new PROCERGS\LoginCidadao\MonitorBundle\PROCERGSLoginCidadaoMonitorBundle(),
            new NFG\BadgesBundle\NFGBadgesBundle(),
            new PROCERGS\LoginCidadao\NfgBundle\PROCERGSNfgBundle(),
            new PROCERGS\LoginCidadao\AccountingBundle\PROCERGSLoginCidadaoAccountingBundle(),
            new Circle\RestClientBundle\CircleRestClientBundle(),
            new PROCERGS\SmsServiceBundle\PROCERGSSmsServiceBundle(),
            new PROCERGS\LoginCidadao\PhoneVerificationBundle\PROCERGSPhoneVerificationBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
