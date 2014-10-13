<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonAddress;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\RemovePersonAddressFormType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PersonAddressController extends Controller
{

    /**
     * @Route("/person/addresses", name="lc_person_addresses")
     * @Template()
     */
    public function listAction()
    {
        $deleteForms = $this->getDeleteForms();

        return compact('deleteForms');
    }

    /**
     * @Route("/person/addresses/new", name="lc_person_addresses_new")
     * @Template()
     */
    public function newAddressAction(Request $request)
    {
        $address = new PersonAddress();
        $form = $this->createForm('lc_person_address', $address);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $address->setPerson($this->getUser());

            $em = $this->getDoctrine()->getManager();
            $em->persist($address);
            $em->flush();

            return $this->redirect($this->generateUrl('lc_person_addresses'));
        }
        $deleteForms = $this->getDeleteForms();

        return compact('form', 'deleteForms');
    }

    /**
     * @Route("/person/addresses/{id}/edit", name="lc_person_addresses_edit")
     * @Template("PROCERGSLoginCidadaoCoreBundle:PersonAddress:newAddress.html.twig")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $addresses = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:PersonAddress');
        $address = $addresses->find($id);
        if ($address->getPerson()->getId() !== $this->getUser()->getId()) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm('lc_person_address', $address);
        $form->handleRequest($this->getRequest());

        if ($form->isValid()) {
            $address->setPerson($this->getUser());
            $em->flush();
            return $this->redirect($this->generateUrl('lc_person_addresses'));
        }
        $deleteForms = $this->getDeleteForms();
        $edit_form = $form->createView();

        return compact('edit_form', 'deleteForms');
    }

    /**
     * @Route("/person/addresses/{id}/remove", name="lc_person_addresses_delete")
     * @Template()
     */
    public function deleteAction($id)
    {
        $translator = $this->get('translator');
        $form = $this->createForm(new RemovePersonAddressFormType());
        $form->handleRequest($this->getRequest());

        if ($form->isValid()) {
            $person = $this->getUser();
            $em = $this->getDoctrine()->getManager();
            $addresses = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:PersonAddress');
            $address = $addresses->find($id);

            try {
                if ($address->getPerson()->getId() !== $person->getId()) {
                    throw new AccessDeniedException();
                }
                $em->remove($address);
                $em->flush();
            } catch (AccessDeniedException $e) {
                $this->get('session')->getFlashBag()->add('error', $translator->trans("Access Denied."));
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add('error', $translator->trans("Wasn't possible to remove this address."));
                $this->get('session')->getFlashBag()->add('error', $e->getMessage());
            }
        } else {
            $this->get('session')->getFlashBag()->add('error', $translator->trans("Wasn't possible to remove this address."));
        }

        return $this->redirect($this->generateUrl('lc_person_addresses'));
    }

    protected function getDeleteForms()
    {
        $person = $this->getUser();
        $deleteForms = array();

        foreach ($person->getAddresses() as $address) {
            $data = array('address_id' => $address->getId());
            $deleteForms[$address->getId()] = $this->createForm(new RemovePersonAddressFormType(), $data)->createView();
        }
        return $deleteForms;
    }

}
