<?php

namespace PROCERGS\LoginCidadao\APIBundle\Controller;

use FOS\RestBundle\Controller\Annotations as REST;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class BadgeController extends BaseController
{
    /**
     * Retrieves all badges available
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Gets the available badges.",
     *   output = {
     *     "class"="PROCERGS\LoginCidadao\BadgesControlBundle\Model\BadgeInterface"
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     * @REST\View(templateVar="badges")
     * @throws NotFoundHttpException
     */
    public function getBadgesAction()
    {
        $badges = $this->get('badges.handler')->getAvailableBadges();
        
        return $badges;
    }
}
