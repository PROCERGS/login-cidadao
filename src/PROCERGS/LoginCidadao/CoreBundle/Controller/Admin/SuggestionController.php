<?php
namespace PROCERGS\LoginCidadao\CoreBundle\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\ContactFormType;
use PROCERGS\LoginCidadao\CoreBundle\Form\Type\SuggestionFilterFormType;

/**
 * @Route("/admin/suggestion")
 */
class SuggestionController extends Controller
{

    /**
     * @Route("/show/{id}", name="lc_admin_sugg_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $sugg = $em->getRepository('PROCERGSLoginCidadaoCoreBundle:ClientSuggestion')->find($id);
        return array(
            'sugg' => $sugg
        );
    }

    /**
     * @Route("/list", name="lc_admin_sugg_list")
     * @Template()
     */
    public function listAction(Request $request)
    {
        $form = $this->createForm(new SuggestionFilterFormType());
        $form = $form->createView();
        return compact('form');
    }

    /**
     * @Route("/listQuery", name="lc_admin_sugg_list_query")
     * @Template()
     */
    public function listQueryAction(Request $request)
    {
        $form = $this->createForm(new SuggestionFilterFormType());
        $form->handleRequest($request);
        $result['suggs'] = null;
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $q = $em->createQueryBuilder();
            $q->select('cs.id, cs.createdAt, SUBSTRING(cs.text,0, 40) shorttext, u.username');
            $q->from('PROCERGSLoginCidadaoCoreBundle:ClientSuggestion', 'cs');
            $q->join('PROCERGSLoginCidadaoCoreBundle:Person', 'u', 'WITH', 'cs.person = u');
            $q->where('1=1');
            $parms = $form->getData();
            if (isset($parms['username'][0])) {
                $q->andWhere('u.username = ?1');
                $q->setParameter('1', $parms['username']);
            }
            if (isset($parms['dateini'])) {
                $q->andWhere('cs.createdAt >= ?2');
                $q->setParameter('2', $parms['dateini']);
            }
            if (isset($parms['dateend'])) {
                $q->andWhere('cs.createdAt <= ?3');
                $q->setParameter('3', $parms['dateend']);
            }
            if (isset($parms['text'][0])) {
                $q->andWhere("cs.text like ?4");
                $q->setParameter('4', '%'.addcslashes($parms['text'], '\\%_').'%');
            }
            $q->addOrderBy('cs.createdAt');
            $result['suggs'] = $q->getQuery()->getResult();
        }
        return $result;
    }
}
