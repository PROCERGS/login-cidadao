<?php

namespace LoginCidadao\CoreBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;

class LoginCidadaoExtension extends \Twig_Extension
{
    protected $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container            
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Returns a list of global functions to add to the existing list.
     *
     * @return array An array of global functions
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('lc_getForm', array($this, 'getForm'),
                array('is_safe' => array('html'))
            ),
            new \Twig_SimpleFunction('lc_getFormFactory',
                array($this, 'getFormFactory'),
                array('is_safe' => array('html'))
            )
        );
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('formatCep', array($this, 'formatCep')),
        );
    }

    public function formatCep($var)
    {
        $var = substr($var, 0, 5).'-'.substr($var, 5, 3);
        return $var;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'login_twig_extension';
    }

    public function getForm($name = 'LoginCidadao\CoreBundle\Form\Type\LoginFormType')
    {
        return $this->container->get('form.factory')
                ->create($name)
                ->createView();
    }

    public function getFormFactory($name = 'fos_user.registration.form.factory')
    {
        return $this->container->get($name)->createForm()->createView();
    }
}
