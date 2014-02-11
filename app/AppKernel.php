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

            new SimpleThings\EntityAudit\SimpleThingsEntityAuditBundle(),
            new FOS\FacebookBundle\FOSFacebookBundle(),
            new HWI\Bundle\OAuthBundle\HWIOAuthBundle(),

            new PROCERGS\Generic\LocaleBundle\PROCERGSGenericLocaleBundle(),
            new PROCERGS\Generic\HWIOAuthProxyBundle\PROCERGSGenericHWIOAuthProxyBundle(),
            new PROCERGS\LoginCidadao\APIBundle\PROCERGSLoginCidadaoAPIBundle(),
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
