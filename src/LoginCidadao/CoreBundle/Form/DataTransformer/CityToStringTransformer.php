<?php

namespace LoginCidadao\CoreBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\ORM\EntityManager;
use LoginCidadao\CoreBundle\Entity\City;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CityToStringTransformer implements DataTransformerInterface
{

    /**
     * @var EntityManager
     */
    private $om;

    /**
     * @param EntityManager $om
     */
    public function __construct(EntityManager $om)
    {
        $this->om = $om;
    }

    /**
     * Transforms an object (City) to a string.
     *
     * @param City|null $city
     * @return string
     */
    public function transform($city)
    {
        if (null === $city) {
            return "";
        }

        $cityName = $city->getName();
        $state = $city->getState();
        $stateName = $state->getAcronym();

        return sprintf("%s, %s", $cityName, $stateName);
    }

    public function reverseTransform($string)
    {
        $data = explode(', ', $string);

        if (!$string || count($data) === 0) {
            return null;
        }

        $states = $this->om->getRepository('LoginCidadaoCoreBundle:State');

        $query = $this->om->createQueryBuilder()
            ->select('c')
            ->from('LoginCidadaoCoreBundle:City', 'c')
            ->innerJoin('LoginCidadaoCoreBundle:State', 's', 'WITH', 'c.state = s');

        switch (count($data)) {
            case 2:
                $state = $states->findOneByString($data[1]);
                $query->andWhere('s = :state')->setParameter('state', $state);
            case 1:
                $query->andWhere('c.name = :cityName')
                    ->setParameter('cityName', $data[0]);
                break;
            default:
                return null;
        }

        $city = $query->getQuery()->getOneOrNullResult();

        if (null === $city) {
            throw new TransformationFailedException(sprintf(
                'Couldn\'t find a city named "%s".', $string
            ));
        }

        return $city;
    }

}
