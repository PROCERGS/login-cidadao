<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Security\TwoFactor;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\TwoFactorProvider as GoogleProvider;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\Util\StringUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GoogleAndBackupCodeProvider extends GoogleProvider implements TwoFactorProviderInterface
{

    /**
     * @var \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator $authenticator
     */
    protected $authenticator;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface $templating
     */
    protected $templating;

    /**
     * @var string $formTemplate
     */
    protected $formTemplate;

    /**
     * @var string $authCodeParameter
     */
    protected $authCodeParameter;

    /**
     * Construct provider for Google authentication
     *
     * @param \Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticator $helper
     * @param \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface                    $templating
     * @param string                                                                        $formTemplate
     * @param string                                                                        $authCodeParameter
     */
    public function __construct(GoogleAuthenticator $authenticator,
                                EngineInterface $templating, $formTemplate,
                                $authCodeParameter)
    {
        $this->authenticator = $authenticator;
        $this->templating = $templating;
        $this->formTemplate = $formTemplate;
        $this->authCodeParameter = $authCodeParameter;
    }

    /**
     * Ask for Google authentication code and fallbacks to backup codes
     *
     * @param  \Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContext $context
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    public function requestAuthenticationCode(AuthenticationContext $context)
    {
        $user = $context->getUser();
        $request = $context->getRequest();
        $session = $context->getSession();
        $authCode = $request->get($this->authCodeParameter);

        // Display and process form
        if ($request->getMethod() == 'POST') {
            $codeCheck = $this->authenticator->checkCode($user, $authCode) == true;
            if ($codeCheck || $this->checkBackupCode($context, $authCode)) {
                $context->setAuthenticated(true);

                return new RedirectResponse($request->getUri());
            } else {
                $session->getFlashBag()->set("two_factor",
                                             "scheb_two_factor.code_invalid");
            }
        }

        // Force authentication code dialog
        return $this->templating->renderResponse(
                $this->formTemplate,
                array('useTrustedOption' => $context->useTrustedOption())
        );
    }

    protected function checkBackupCode(AuthenticationContext $context, $authCode)
    {
        $person = $context->getUser();
        $backupCodes = $person->getBackupCodes();

        foreach ($backupCodes as $backupCode) {
            if (StringUtils::equals($backupCode->getCode(), $authCode)) {
                $backupCode->setUsed(true);
                // TODO: persist used backup code

                return true;
            }
        }
        return false;
    }

}
