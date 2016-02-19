<?php

namespace LoginCidadao\CoreBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use LoginCidadao\OAuthBundle\Entity\Client;

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
            new \Twig_SimpleFunction('lc_getForm', 'getForm',
                array('is_safe' => array('html'))
            ),
            new \Twig_SimpleFunction('lc_getFormFactory', 'getFormFactory',
                array('is_safe' => array('html'))
            ),
            new \Twig_SimpleFunction('lc_render', 'lcRender',
                array('is_safe' => array('html'))
            ),
            new \Twig_SimpleFunction('lc_client_picture_web_path',
                'lcClientPictureWebPath', array('is_safe' => array('html'))
            )
        );
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('formatCep', array($this, 'formatCep')),
            new \Twig_SimpleFilter('formatCpf', array($this, 'formatCpf')),
        );
    }

    public function formatCep($var)
    {
        $var = substr($var, 0, 5).'-'.substr($var, 5, 3);
        return $var;
    }

    public function formatCpf($var)
    {
        $var = substr($var, 0, 3).'.'.substr($var, 3, 3).'.'.substr($var, 6, 3).'-'.substr($var,
                9);
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

    public function getForm($name = 'lc.login.form.type')
    {
        return $this->container->get('form.factory')
                ->create($this->container->get($name))
                ->createView();
    }

    public function getFormFactory($name = 'fos_user.registration.form.factory')
    {
        return $this->container->get($name)->createForm()->createView();
    }

    public function lcRender($name)
    {
        return '';
    }

    public function lcClientPictureWebPath($var)
    {
        return Client::resolvePictureWebPath($var);
    }
}
