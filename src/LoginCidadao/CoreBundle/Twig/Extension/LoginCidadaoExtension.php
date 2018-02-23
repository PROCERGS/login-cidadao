<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Twig\Extension;

use FOS\UserBundle\Form\Factory\FactoryInterface;
use Symfony\Component\Form\FormFactoryInterface;

class LoginCidadaoExtension extends \Twig_Extension
{
    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var FactoryInterface */
    private $registrationFormFactory;

    /**
     * Constructor.
     *
     * @param FormFactoryInterface $formFactory
     * @param FactoryInterface $registrationFormFactory
     */
    public function __construct(FormFactoryInterface $formFactory, FactoryInterface $registrationFormFactory)
    {
        $this->formFactory = $formFactory;
        $this->registrationFormFactory = $registrationFormFactory;
    }

    /**
     * Returns a list of global functions to add to the existing list.
     *
     * @return array An array of global functions
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('lc_getForm', [$this, 'getForm'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('lc_getFormFactory', [$this, 'getFormFactory'], ['is_safe' => ['html']]),
        );
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('formatCep', [$this, 'formatCep']),
            new \Twig_SimpleFilter('formatCpf', [$this, 'formatCpf']),
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

    public function getForm($name = 'LoginCidadao\CoreBundle\Form\Type\LoginFormType')
    {
        return $this->formFactory
            ->create($name)
            ->createView();
    }

    public function getFormFactory()
    {
        return $this->registrationFormFactory->createForm()->createView();
    }
}
