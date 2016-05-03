<?php

namespace LoginCidadao\APIBundle\Controller;

use FOS\RestBundle\Controller\Annotations as REST;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use LoginCidadao\APIBundle\Security\Audit\Annotation as Audit;

class BadgeController extends BaseController
{
    /**
     * Retrieves all badges available
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Gets the available badges.",
     *   output = "ArrayCollection",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     * @REST\View(templateVar="badges")
     * @Audit\Loggable(type="SELECT")
     * @throws NotFoundHttpException
     */
    public function getBadgesAction()
    {
        $badges = $this->get('badges.handler')->getAvailableBadges();

        return $badges;
    }
}
