<?php

namespace LoginCidadao\CoreBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\ORM\EntityManager;
use LoginCidadao\CoreBundle\Entity\Country;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CountryToStringTransformer implements DataTransformerInterface
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
     * Transforms an object (Country) to a string.
     *
     * @param Country|null $country
     * @return string
     */
    public function transform($country)
    {
        if (null === $country) {
            return "";
        }

        return $country->getName();
    }

    public function reverseTransform($string)
    {

        if (!$string) {
            return null;
        }

        $query = $this->om->createQueryBuilder()
            ->select('c')
            ->from('LoginCidadaoCoreBundle:Country', 'c')
            ->where('c.name = :countryName')
            ->setParameter('countryName', $string);

        $country = $query->getQuery()->getOneOrNullResult();

        if (null === $country) {
            throw new TransformationFailedException(sprintf(
                'Couldn\'t find a country named "%s".', $string
            ));
        }

        return $country;
    }

}
