<?php

namespace LoginCidadao\CoreBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use LoginCidadao\CoreBundle\Form\Type\SuggestionFilterFormType;
use LoginCidadao\CoreBundle\Helper\GridHelper;

/**
 * @Route("/admin/suggestion")
 */
class SuggestionController extends Controller
{

    /**
     * @Route("/", name="lc_admin_sugg")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $form = $this->createForm('LoginCidadao\CoreBundle\Form\Type\SuggestionFilterFormType');
        $form = $form->createView();
        return compact('form');
    }

    /**
     * @Route("/listQuery", name="lc_admin_sugg_list_query")
     * @Template()
     */
    public function listQueryAction(Request $request)
    {
        $form           = $this->createForm('LoginCidadao\CoreBundle\Form\Type\SuggestionFilterFormType');
        $form->handleRequest($request);
        $result['grid'] = null;
        if ($form->isValid()) {
            $em    = $this->getDoctrine()->getManager();
            $sql   = $em->createQueryBuilder();
            $sql->select('cs.id, cs.createdAt, cs.text shorttext, u.username');
            $sql->from('LoginCidadaoCoreBundle:ClientSuggestion', 'cs');
            $sql->join('LoginCidadaoCoreBundle:Person', 'u', 'WITH',
                'cs.person = u');
            $sql->where('1=1');
            $parms = $form->getData();
            if (isset($parms['username'][0])) {
                $sql->andWhere('u.username = ?1');
                $sql->setParameter('1', $parms['username']);
            }
            if (isset($parms['dateini'])) {
                $sql->andWhere('cs.createdAt >= ?2');
                $sql->setParameter('2', $parms['dateini']);
            }
            if (isset($parms['dateend'])) {
                $sql->andWhere('cs.createdAt <= ?3');
                $sql->setParameter('3', $parms['dateend']);
            }
            if (isset($parms['text'][0])) {
                $sql->andWhere("cs.text like ?4");
                $sql->setParameter('4',
                    '%'.addcslashes($parms['text'], '\\%_').'%');
            }
            $sql->addOrderBy('cs.createdAt');

            $grid = new GridHelper();
            $grid->setId('suggs-grid');
            $grid->setPerPage(5);
            $grid->setMaxResult(5);
            $grid->setQueryBuilder($sql);
            $grid->setInfiniteGrid(true);
            $grid->setRoute('lc_admin_sugg_list_query');
            $grid->setRouteParams(array($form->getName()));
            return array('grid' => $grid->createView($request));
        }
        return $result;
    }
}
