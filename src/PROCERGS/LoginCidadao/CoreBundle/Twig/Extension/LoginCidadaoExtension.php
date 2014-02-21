<?php
namespace PROCERGS\LoginCidadao\CoreBundle\Twig\Extension;

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
        return array('logincidadao_initializeForm' => new \Twig_Function_Method($this, 'initializeForm', array('is_safe' => array('html'))));
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

    /**
     *
     * @see FacebookHelper::initialize()
     */
    public function initializeForm($parameters = array(), $name = null)
    {
        $form = $this->container->get('form.factory')->create($this->container->get('procergs_logincidadao.login.form.type'));
        return $form->createView();
    }

}
