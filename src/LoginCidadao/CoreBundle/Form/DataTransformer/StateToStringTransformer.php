<?php

namespace LoginCidadao\CoreBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\ORM\EntityManager;
use LoginCidadao\CoreBundle\Entity\State;
use Symfony\Component\Form\Exception\TransformationFailedException;

class StateToStringTransformer implements DataTransformerInterface
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
     * Transforms an object (State) to a string.
     *
     * @param State|null $state
     * @return string
     */
    public function transform($state)
    {
        if (null === $state) {
            return "";
        }

        $stateName = $state->getName();
        $country = $state->getCountry();
        $countryName = $country->getIso2();

        return sprintf("%s, %s", $stateName, $countryName);
    }

    public function reverseTransform($string)
    {
        $data = explode(', ', $string);

        if (!$string || count($data) === 0) {
            return null;
        }

        $countries = $this->om->getRepository('LoginCidadaoCoreBundle:State');

        $query = $this->om->createQueryBuilder()
            ->select('s')
            ->from('LoginCidadaoCoreBundle:State', 's')
            ->innerJoin('LoginCidadaoCoreBundle:Country', 'c', 'WITH', 's.country = c');

        switch (count($data)) {
            case 2:
                $country = $countries->findOneByString($data[1]);
                $query->andWhere('c = :country')->setParameter('country', $country);
            case 1:
                $query->andWhere('s.name = :stateName')
                    ->setParameter('stateName', $data[0]);
                break;
            default:
                return null;
        }

        $state = $query->getQuery()->getOneOrNullResult();

        if (null === $state) {
            throw new TransformationFailedException(sprintf(
                'Couldn\'t find a state named "%s".', $string
            ));
        }

        return $state;
    }

}
