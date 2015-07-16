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
            new PROCERGS\OAuthBundle\PROCERGSOAuthBundle(),
            new PROCERGS\LoginCidadao\CoreBundle\PROCERGSLoginCidadaoCoreBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new FOS\RestBundle\FOSRestBundle(),

            new SimpleThings\EntityAudit\SimpleThingsEntityAuditBundle(),
            new FOS\FacebookBundle\FOSFacebookBundle(),
            new HWI\Bundle\OAuthBundle\HWIOAuthBundle(),
            new Donato\Generic\HWIOAuthProxyBundle\DonatoGenericHWIOAuthProxyBundle(),

            new PROCERGS\Generic\LocaleBundle\PROCERGSGenericLocaleBundle(),
            new PROCERGS\LoginCidadao\APIBundle\PROCERGSLoginCidadaoAPIBundle(),
            new PROCERGS\Generic\ValidationBundle\PROCERGSGenericValidationBundle(),
            new EWZ\Bundle\RecaptchaBundle\EWZRecaptchaBundle(),

            new Knp\Bundle\GaufretteBundle\KnpGaufretteBundle(),
            new Vich\UploaderBundle\VichUploaderBundle(),

            new JMS\DiExtraBundle\JMSDiExtraBundle($this),
            new JMS\AopBundle\JMSAopBundle(),
            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
            new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
            
            new PROCERGS\LoginCidadao\BadgesControlBundle\PROCERGSLoginCidadaoBadgesControlBundle(),
            new PROCERGS\LoginCidadao\BadgesBundle\PROCERGSLoginCidadaoBadgesBundle(),
            new PROCERGS\LoginCidadao\NotificationBundle\PROCERGSLoginCidadaoNotificationBundle(),
            
            new Scheb\TwoFactorBundle\SchebTwoFactorBundle(),
            new PROCERGS\LoginCidadao\IgpBundle\PROCERGSLoginCidadaoIgpBundle(),
            new PROCERGS\LoginCidadao\ValidationControlBundle\PROCERGSLoginCidadaoValidationControlBundle(),

            new LoginCidadao\TOSBundle\LoginCidadaoTOSBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
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
