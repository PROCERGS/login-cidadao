<?php
namespace LoginCidadao\CoreBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class FromArray implements DataTransformerInterface
{
    public function transform($issue)
    {
        if (null === $issue) {
            return "";
        }
        
        return implode("\r\n", $issue);
    }

    public function reverseTransform($number)
    {
        if (!$number) {
            return null;
        }
        
        $issue = explode("\r\n", $number);
        
        if (null === $issue) {
            throw new TransformationFailedException(sprintf('An issue with number "%s" does not exist!', $number));
        }
        
        return $issue;
    }
}
