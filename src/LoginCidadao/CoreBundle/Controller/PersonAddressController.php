<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\CoreBundle\Controller;

use LoginCidadao\CoreBundle\Model\LocationSelectData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use LoginCidadao\CoreBundle\Entity\PersonAddress;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\FormError;
use LoginCidadao\CoreBundle\Entity\State;
use LoginCidadao\CoreBundle\Entity\City;

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
        $form = $this->createForm('LoginCidadao\CoreBundle\Form\Type\PersonAddressFormType', $address);

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
        $isPreferred = $em->getRepository('LoginCidadaoCoreBundle:Country')->isPreferred($address->getCountry());
        if (!$address->getState() && !$isPreferred) {
            $steppe = ucwords(strtolower(trim($form->get('statesteppe')->getData())));
            if ($steppe) {
                $repo = $em->getRepository('LoginCidadaoCoreBundle:State');
                $ent = $repo->findOneBy(array(
                    'name' => $steppe,
                    'country' => $address->getCountry(),
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
                $repo = $em->getRepository('LoginCidadaoCoreBundle:City');
                $ent = $repo->findOneBy(array(
                    'name' => $steppe,
                    'state' => $state,
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
     * @Template("LoginCidadaoCoreBundle:PersonAddress:newAddress.html.twig")
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $addresses = $em->getRepository('LoginCidadaoCoreBundle:PersonAddress');
        /** @var PersonAddress $address */
        $address = $addresses->find($id);
        if ($address->getPerson()->getId() !== $this->getUser()->getId()) {
            throw new AccessDeniedException();
        }

        $address = $this->prepareAddressForEdition($address);

        $form = $this->createForm('LoginCidadao\CoreBundle\Form\Type\PersonAddressFormType', $address);
        $form->handleRequest($request);

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
    public function deleteAction(Request $request, $id)
    {
        $translator = $this->get('translator');
        $form = $this->createForm('LoginCidadao\CoreBundle\Form\Type\RemovePersonAddressFormType');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $person = $this->getUser();
            $em = $this->getDoctrine()->getManager();
            $addresses = $em->getRepository('LoginCidadaoCoreBundle:PersonAddress');
            $address = $addresses->find($id);

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
        $person = $this->getUser();
        $deleteForms = array();
        $addresses = $person->getAddresses();

        if (is_array($addresses) || $addresses instanceof Collection) {
            foreach ($addresses as $address) {
                $data = array('address_id' => $address->getId());
                $deleteForms[$address->getId()] = $this->createForm(
                    'LoginCidadao\CoreBundle\Form\Type\RemovePersonAddressFormType',
                    $data)
                    ->createView();
            }
        }

        return $deleteForms;
    }

    private function prepareAddressForEdition(PersonAddress $address)
    {
        if ($address->getId() > 0) {
            $city = $address->getCity();
            if ($city instanceof City) {
                if (!$address->getLocation() instanceof LocationSelectData) {
                    $address->setLocation(new LocationSelectData());
                }
                $state = $city->getState();
                $country = $state->getCountry();
                $address->getLocation()->setCity($city)
                    ->setState($state)->setCountry($country);
            }
        }

        return $address;
    }
}
