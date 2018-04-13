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
     * @return \Twig_SimpleFunction[] An array of global functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('lc_getForm', [$this, 'getForm'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('lc_getFormFactory', [$this, 'getFormFactory'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @return array|\Twig_SimpleFilter[]
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('formatCep', [$this, 'formatCep']),
            new \Twig_SimpleFilter('formatCpf', [$this, 'formatCpf']),
        ];
    }

    public function formatCep($cep)
    {
        return preg_replace('/(\d{5})(\d{3})/', '$1-$2', $cep);
    }

    public function formatCpf($cpf)
    {
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
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
