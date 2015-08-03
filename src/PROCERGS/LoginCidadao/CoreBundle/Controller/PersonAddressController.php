<?php

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PROCERGS\LoginCidadao\CoreBundle\Entity\PersonAddress;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\RemovePersonAddressFormType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\FormError;
use PROCERGS\LoginCidadao\CoreBundle\Entity\State;
use PROCERGS\LoginCidadao\CoreBundle\Entity\City;

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
        $form    = $this->createForm('lc_person_address', $address);

        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isValid()) {
            $address->setPerson($this->getUser());
            $em->persist($address);
            $em->flush();

            return $this->redirect($this->generateUrl('lc_person_addresses'));
        }
        $deleteForms = $this->getDeleteForms();

        return compact('form', 'deleteForms');
    }

    private function checkAddressLocation(&$address, $form, &$em)
    {
        $form = $form->get('location');
        if (!$address->getCountry()) {
            $form->get('country')->addError(new FormError($this->get('translator')->trans('required.field')));
            return false;
        }
        $isPreferred = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:Country')->isPreferred($address->getCountry());
        if (!$address->getState() && !$isPreferred) {
            $steppe = ucwords(strtolower(trim($form->get('statesteppe')->getData())));
            if ($steppe) {
                $repo = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:State');
                $ent  = $repo->findOneBy(array(
                    'name' => $steppe,
                    'country' => $address->getCountry()
                ));
                if (!$ent) {
                    $ent = new State();
                    $ent->setName($steppe);
                    $ent->setCountry($address->getCountry());
                    $em->persist($ent);
                }
                $address->setState($ent);
            }
        }
        if (!$address->getState()) {
            $form->get('state')->addError(new FormError($this->get('translator')->trans('required.field')));
            return false;
        }
        if (!$address->getCity() && !$isPreferred) {
            $steppe = ucwords(strtolower(trim($form->get('citysteppe')->getData())));
            if ($address->getState()) {
                $state = $address->getState();
            } elseif (isset($ent)) {
                $state = $ent;
            } else {
                $state = null;
            }
            if ($state && $steppe) {
                $repo = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:City');
                $ent  = $repo->findOneBy(array(
                    'name' => $steppe,
                    'state' => $state
                ));
                if (!$ent) {
                    $ent = new City();
                    $ent->setName($steppe);
                    $ent->setState($state);
                    $em->persist($ent);
                }
                $address->setCity($ent);
            }
        }
        if (!$address->getCity()) {
            $form->get('city')->addError(new FormError($this->get('translator')->trans('required.field')));
            return false;
        }
        return true;
    }

    /**
     * @Route("/person/addresses/{id}/edit", name="lc_person_addresses_edit")
     * @Template("PROCERGSLoginCidadaoCoreBundle:PersonAddress:newAddress.html.twig")
     */
    public function editAction($id)
    {
        $em        = $this->getDoctrine()->getManager();
        $addresses = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:PersonAddress');
        $address   = $addresses->find($id);
        if ($address->getPerson()->getId() !== $this->getUser()->getId()) {
            throw new AccessDeniedException();
        }
        if ($address->getId() > 0) {
            $city = $address->getCity();
            if ($city instanceof City) {
                $state   = $city->getState();
                $country = $state->getCountry();
                $address->getLocation()->setCity($city)
                    ->setState($state)->setCountry($country);
            }
        }

        $form = $this->createForm('lc_person_address', $address);
        $form->handleRequest($this->getRequest());

        if ($form->isValid()) {
            $address->setPerson($this->getUser());
            
            $em->flush();
            return $this->redirect($this->generateUrl('lc_person_addresses'));
        }
        $deleteForms = $this->getDeleteForms();
        $edit_form   = $form->createView();

        return compact('edit_form', 'deleteForms');
    }

    /**
     * @Route("/person/addresses/{id}/remove", name="lc_person_addresses_delete")
     * @Template()
     */
    public function deleteAction($id)
    {
        $translator = $this->get('translator');
        $form       = $this->createForm(new RemovePersonAddressFormType());
        $form->handleRequest($this->getRequest());

        if ($form->isValid()) {
            $person    = $this->getUser();
            $em        = $this->getDoctrine()->getManager();
            $addresses = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:PersonAddress');
            $address   = $addresses->find($id);

            try {
                if ($address->getPerson()->getId() !== $person->getId()) {
                    throw new AccessDeniedException();
                }
                $em->remove($address);
                $em->flush();
            } catch (AccessDeniedException $e) {
                $this->get('session')->getFlashBag()->add('error',
                    $translator->trans("Access Denied."));
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add('error',
                    $translator->trans("Wasn't possible to remove this address."));
                $this->get('session')->getFlashBag()->add('error',
                    $e->getMessage());
            }
        } else {
            $this->get('session')->getFlashBag()->add('error',
                $translator->trans("Wasn't possible to remove this address."));
        }

        return $this->redirect($this->generateUrl('lc_person_addresses'));
    }

    protected function getDeleteForms()
    {
        $person      = $this->getUser();
        $deleteForms = array();
        $addresses   = $person->getAddresses();

        if (is_array($addresses) || $addresses instanceof Collection) {
            foreach ($addresses as $address) {
                $data                           = array('address_id' => $address->getId());
                $deleteForms[$address->getId()] = $this->createForm(new RemovePersonAddressFormType(),
                        $data)->createView();
            }
        }
        return $deleteForms;
    }
}