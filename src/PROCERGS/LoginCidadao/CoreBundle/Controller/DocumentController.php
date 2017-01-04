<?php
/*
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\LoginCidadao\CoreBundle\Controller;

use LoginCidadao\CoreBundle\Controller\DocumentController as BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class DocumentController extends BaseController
{

    /**
     * @Route("/person/documents/general", name="lc_documents_general")
     * @Template()
     */
    public function generalAction(Request $request)
    {
        $response = parent::generalAction($request);

        if (is_array($response)) {
            $meuRSHelper = $this->get('meurs.helper');

            $response['personMeuRS'] = $meuRSHelper->getPersonMeuRS($this->getUser(), true);
            $response['tre_search_link'] = $this->getParameter('tre_search_link');
        }

        return $response;
    }
}
