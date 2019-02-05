<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\SupportBundle\Controller;

use Doctrine\ORM\NonUniqueResultException;
use LoginCidadao\CoreBundle\Entity\PersonRepository;
use LoginCidadao\CoreBundle\Entity\SentEmail;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use LoginCidadao\SupportBundle\Form\PersonSearchFormType;
use LoginCidadao\SupportBundle\Model\PersonSearchRequest;
use LoginCidadao\SupportBundle\Service\SupportHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PersonSupportController
 * @package LoginCidadao\SupportBundle\Controller
 *
 * @Security("has_role('ROLE_SUPPORT_AGENT')")
 */
class PersonSupportController extends Controller
{
    /**
     * @Route("/support/search", name="lc_support_person_search")
     */
    public function indexAction(Request $request)
    {
        $search = new PersonSearchRequest();

        $search->smartSearch = $request->get('search', null);

        $form = $this->createForm(PersonSearchFormType::class, $search);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var PersonRepository $repo */
            $repo = $this->getDoctrine()->getRepository('LoginCidadaoCoreBundle:Person');
            try {
                $person = $repo->getSmartSearchQuery($search->smartSearch)
                    ->getQuery()->getOneOrNullResult();

                if ($person instanceof PersonInterface) {
                    return $this->redirectToRoute('lc_support_person_view', [
                        'id' => $person->getId(),
                        'ticket' => $search->supportTicket,
                    ]);
                }
            } catch (NonUniqueResultException $e) {
                // TODO: regular search
            }

            dump("FORM OK");
        }

        return $this->render('LoginCidadaoSupportBundle:PersonSupport:index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/support/person/{id}", name="lc_support_person_view")
     */
    public function viewAction(Request $request, $id)
    {
        $supportRequest = $this->validateSupportTicketId($request->get('ticket'));

        /** @var SupportHandler $supportHandler */
        $supportHandler = $this->get(SupportHandler::class);

        $person = $supportHandler->getSupportPerson($id);

        return $this->render('LoginCidadaoSupportBundle:PersonSupport:view.html.twig', [
            'person' => $person,
            'supportRequest' => $supportRequest,
            'dataValidation' => $supportHandler->getValidationMap($person),
        ]);
    }

    private function validateSupportTicketId(string $ticket = null): SentEmail
    {
        /** @var SupportHandler $supportHandler */
        $supportHandler = $this->get(SupportHandler::class);
        $sentEmail = $ticket ? $supportHandler->getInitialMessage($ticket) : null;
        if (!$sentEmail instanceof SentEmail) {
            throw $this->createNotFoundException("Invalid Support Ticket ID");
        }

        return $sentEmail;
    }
}
