<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Form\Type;

use Beelab\Recaptcha2Bundle\Validator\Constraints\Recaptcha2;
use Doctrine\ORM\EntityManagerInterface;
use LoginCidadao\CoreBundle\Entity\AccessSession;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginFormType extends AbstractType
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var Request */
    private $request;

    /** @var int */
    private $bruteForceThreshold;

    /** @var bool */
    private $verifyCaptcha;

    /**
     * @param ContainerInterface $container
     * @throws \Exception
     */
    public function setContainer(ContainerInterface $container)
    {
        /** @var RequestStack $requestStack */
        $requestStack = $container->get('request_stack');

        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->request = $requestStack->getCurrentRequest();
        $this->bruteForceThreshold = $container->getParameter('brute_force_threshold');
    }

    /**
     * @return bool
     */
    private function shouldVerifyCaptcha()
    {
        if ($this->verifyCaptcha === null) {
            $session = $this->request->getSession();
            if (null !== $session) {
                $lastUsername = $session->get(Security::LAST_USERNAME);
                $vars = [
                    'ip' => $this->request->getClientIp(),
                    'username' => $lastUsername,
                ];
                /** @var AccessSession|null $accessSession */
                $accessSession = $this->em->getRepository('LoginCidadaoCoreBundle:AccessSession')
                    ->findOneBy($vars);
                $this->verifyCaptcha = $accessSession && $accessSession->getVal() >= $this->bruteForceThreshold;
            }
        }

        return $this->verifyCaptcha;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @throws \Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->verifyCaptcha = $options['check_captcha'];

        $builder->add(
            'username',
            'Symfony\Component\Form\Extension\Core\Type\TextType',
            ['label' => 'security.login.username']
        );
        $builder->add(
            'password',
            'Symfony\Component\Form\Extension\Core\Type\PasswordType',
            ['label' => 'security.login.password', 'attr' => ['autocomplete' => 'off'], 'mapped' => false]
        );

        if ($this->shouldVerifyCaptcha()) {
            $builder->add(
                'recaptcha',
                'Beelab\Recaptcha2Bundle\Form\Type\RecaptchaType',
                ['label' => false, 'mapped' => false, 'constraints' => new Recaptcha2()]
            );
        }
    }

    public function getBlockPrefix()
    {
        return 'login_form_type';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => 'csrf_token',
            'csrf_token_id' => 'authenticate',
            'check_captcha' => null,
        ]);
    }
}
