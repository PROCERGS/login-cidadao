<?php

namespace LoginCidadao\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use LoginCidadao\CoreBundle\Form\Type\RemoveIdCardFormType;
use LoginCidadao\CoreBundle\Model\PersonInterface;
use Doctrine\Common\Collections\Collection;
use LoginCidadao\CoreBundle\Entity\IdCard;
use LoginCidadao\ValidationControlBundle\Handler\ValidationHandler;

class IdCardController extends Controller
{

    /**
     * @Route("/person/idcards", name="lc_person_id_cards_list")
     * @Template
     */
    public function listAction(Request $request)
    {
        $idCards     = $this->getIdCards();
        $deleteForms = $this->getDeleteForms($idCards);
        $states      = $this->getDoctrine()->getRepository('LoginCidadaoCoreBundle:State')->findStateByPreferredCountry($this->container->getParameter('lc_idcard_country_acronym'));
        return compact('idCards', 'deleteForms', 'states');
    }

    /**
     * @Route("/person/idcards/new", name="lc_person_id_cards_new")
     * @Template
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $stateId = $request->get('state');
        $state   = null;
        if ($stateId > 0) {
            $state = $em->getRepository('LoginCidadaoCoreBundle:State')
                ->find($stateId);
        }

        $validationHandler = $this->getValidationHandler();
        $idCard            = $validationHandler->instantiateIdCard($state);

        $idCard->setPerson($this->getPerson());
        $form = $this->createForm('lc_idcard_form', $idCard);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em->persist($form->getData());
            $validationHandler->persistIdCard($form, $request);
            $em->flush();

            return $this->redirect($this->generateUrl('lc_documents'));
        }

        return array(
            'form' => $form->createView(),
            'deleteForms' => $this->getDeleteForms()
        );
    }

    /**
     * @Route("/person/idcards/{id}/edit", name="lc_person_id_cards_edit")
     * @Template
     */
    public function editAction(Request $request, $id)
    {
        $fragment          = $request->query->has('fragment');
        $em                = $this->getDoctrine()->getManager();
        $person            = $this->getUser();
        $validationHandler = $this->getValidationHandler();

        $idCards = $em->getRepository('LoginCidadaoCoreBundle:IdCard');
        $idCard  = $idCards->findPersonIdCard($person, $id);

        $form = $this->createForm('lc_idcard_form', $idCard);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->persist($form->getData());
            $validationHandler->persistIdCard($form, $request);
            $em->flush();
            return $this->redirect($this->generateUrl('lc_documents'));
        }

        return array(
            'form' => $form->createView(),
            'fragment' => $fragment,
            'id' => $id,
            'deleteForms' => $this->getDeleteForms()
        );
    }

    /**
     * @Route("/person/idcards/{id}/remove", name="lc_person_id_cards_delete")
     * @Template()
     */
    public function deleteAction(Request $request, $id)
    {
        $translator = $this->get('translator');
        $form       = $this->createForm('LoginCidadao\CoreBundle\Form\Type\RemoveIdCardFormType');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $person  = $this->getUser();
            $em      = $this->getDoctrine()->getManager();
            $idCards = $em->getRepository('LoginCidadaoCoreBundle:IdCard');
            $idCard  = $idCards->find($id);

            try {
                if ($idCard->getPerson()->getId() !== $person->getId()) {
                    throw new AccessDeniedException();
                }
                $em->remove($idCard);
                $em->flush();
            } catch (AccessDeniedException $e) {
                $this->get('session')->getFlashBag()->add('error',
                    $translator->trans("Access Denied."));
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add('error',
                    $translator->trans("Couldn't remove this ID Card."));
                $this->get('session')->getFlashBag()->add('error',
                    $e->getMessage());
            }
        } else {
            $this->get('session')->getFlashBag()->add('error',
                $translator->trans("Couldn't remove this ID Card."));
        }
        return $this->redirect($this->generateUrl('lc_documents'));
    }

    /**
     * @Route("/person/idcards/{id}/add-new-button", name="lc_person_id_cards_add_new_button")
     * @Template
     */
    public function addNewButtonAction()
    {
        $preferredCountry = $this->container->getParameter('lc_idcard_country_acronym');

        $states = $this->getDoctrine()
            ->getRepository('LoginCidadaoCoreBundle:State')
            ->findStateByPreferredCountry($preferredCountry);

        return compact('states');
    }

    protected function getDeleteForms($idCards = null)
    {
        $deleteForms = array();
        if ($idCards === null) {
            $idCards = $this->getIdCards();
        }

        if (is_array($idCards) || $idCards instanceof Collection) {
            foreach ($idCards as $idCard) {
                $data                          = array('id_card_id' => $idCard->getId());
                $deleteForms[$idCard->getId()] = $this->createForm(
                        'LoginCidadao\CoreBundle\Form\Type\RemoveIdCardFormType',
                        $data)
                    ->createView();
            }
        }
        return $deleteForms;
    }

    /**
     * @return PersonInterface
     */
    protected function getPerson()
    {
        return $this->getUser();
    }

    protected function getIdCards()
    {
        $person = $this->getPerson();
        $repo   = $this->getDoctrine()->getRepository('LoginCidadaoCoreBundle:IdCard');
        return $repo->findByPersonOrderByStateAcronym($person);
    }

    /**
     * @return ValidationHandler
     */
    private function getValidationHandler()
    {
        return $this->get('validation.handler');
    }
}
