<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\AccountRecoveryBundle\Controller;

use LoginCidadao\AccountRecoveryBundle\Form\AccountRecoveryDataType;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\AccountRecoveryBundle\Service\AccountRecoveryService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class AccountRecoveryDataController extends Controller
{
    /**
     * @Route("/account-recovery-data", name="account_recovery_edit")
     * @Template()
     */
    public function editAction(Request $request)
    {
        /** @var PersonInterface $person */
        $person = $this->getUser();

        /** @var AccountRecoveryService $accountRecoveryService */
        $accountRecoveryService = $this->get('lc.account_recovery');

        $recoveryData = $accountRecoveryService->getAccountRecoveryData($person);
        $form = $this->createForm(AccountRecoveryDataType::class, $recoveryData);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('account_recovery_edit');
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
