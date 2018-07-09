<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LoginCidadao\RemoteClaimsBundle\Tests\Validator\Constraints;

use LoginCidadao\RemoteClaimsBundle\Entity\RemoteClaim;
use LoginCidadao\RemoteClaimsBundle\Model\ClaimProviderInterface;
use LoginCidadao\RemoteClaimsBundle\Model\TagUri;
use LoginCidadao\RemoteClaimsBundle\Validator\Constraints\HostBelongsToClaimProvider;
use LoginCidadao\RemoteClaimsBundle\Validator\Constraints\HostBelongsToClaimProviderValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class HostBelongsToClaimProviderValidatorTest extends TestCase
{
    public function testValidateOk()
    {
        $claimName = TagUri::createFromString('tag:example.com,2017:my_claim');
        $value = $this->getRemoteClaim($claimName, ['https://my.example.com/', 'https://example.com/valid']);

        $validator = new HostBelongsToClaimProviderValidator();
        $validator->validate($value, new HostBelongsToClaimProvider());
    }

    public function testValidateFail()
    {
        $host = 'example.com';
        $claimName = TagUri::createFromString("tag:{$host},2017:my_claim");

        $violationBuilder = $this->createMock('Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface');
        $violationBuilder->expects($this->once())->method('addViolation');
        $violationBuilder->expects($this->once())->method('setParameter')
            ->with('{{ host }}', $host)->willReturn($violationBuilder);

        /** @var ExecutionContextInterface|\PHPUnit_Framework_MockObject_MockObject $context */
        $context = $this->createMock('Symfony\Component\Validator\Context\ExecutionContextInterface');
        $context->expects($this->once())->method('buildViolation')->willReturn($violationBuilder);

        $value = $this->getRemoteClaim($claimName, ['https://my.example.com/']);

        $validator = new HostBelongsToClaimProviderValidator();
        $validator->initialize($context);
        $validator->validate($value, new HostBelongsToClaimProvider());
    }

    private function getRemoteClaim($claimName, $redirectUris)
    {
        /** @var ClaimProviderInterface|\PHPUnit_Framework_MockObject_MockObject $provider */
        $provider = $this->createMock('LoginCidadao\RemoteClaimsBundle\Model\ClaimProviderInterface');
        $provider->expects($this->once())->method('getRedirectUris')
            ->willReturn($redirectUris);

        $remoteClaim = (new RemoteClaim())
            ->setName($claimName)
            ->setProvider($provider);

        return $remoteClaim;
    }
}
