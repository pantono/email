<?php

namespace Pantono\Email\Tests;

use PHPUnit\Framework\TestCase;
use Pantono\Email\Repository\EmailAddressRepository;
use Pantono\Hydrator\Hydrator;
use PHPUnit\Framework\MockObject\MockObject;
use Pantono\Email\EmailAddresses;
use Pantono\Email\Model\EmailAddress;
use Egulias\EmailValidator\EmailValidator;
use Pantono\Email\Model\EmailConfig;

class EmailAddressesTest extends TestCase
{
    private EmailAddressRepository|MockObject $repository;
    private Hydrator|MockObject $hydrator;
    private EmailValidator $validator;

    public function setUp(): void
    {
        $this->repository = $this->getMockBuilder(EmailAddressRepository::class)->disableOriginalConstructor()->getMock();
        $this->hydrator = $this->getMockBuilder(Hydrator::class)->disableOriginalConstructor()->getMock();
        $this->validator = new EmailValidator();
    }

    public function testValidateEmailOK()
    {
        $date = new \DateTimeImmutable();
        $mock = $this->getMockWithMethods(['getEmailByAddress', 'getConfig']);
        $mock->expects($this->once())
            ->method('getEmailByAddress')
            ->willReturn(null);
        $config = new EmailConfig();
        $config->setCheckDns(true);
        $config->setCheckSmtp(false);
        $config->setCheckDisposableDomain(true);

        $mock->expects($this->once())
            ->method('getConfig')
            ->willReturn($config);

        $mockEmail = new EmailAddress();
        $mockEmail->setEmail('test@test.com');
        $mockEmail->setValid(true);
        $mockEmail->setLastChecked($date);
        $mockEmail->setInvalidReason(null);
        $this->repository->expects($this->once())
            ->method('saveEmailAddress')
            ->with($mockEmail);

        $this->assertEquals(true, $mock->validateEmailAddress('test@test.com', $date));
    }

    public function testDisposableDomainThrowsError()
    {
        $date = new \DateTimeImmutable();
        $this->repository->expects($this->once())
            ->method('getDisposableEmailDomain')
            ->with('test.com')
            ->willReturn(['domain' => 'test.com']);
        $mock = $this->getMockWithMethods(['getEmailByAddress', 'getConfig']);
        $mock->expects($this->once())
            ->method('getEmailByAddress')
            ->willReturn(null);
        $mock->expects($this->once())
            ->method('getConfig')
            ->willReturn($this->createConfig(true, true, false));

        $mockEmail = new EmailAddress();
        $mockEmail->setEmail('test@test.com');
        $mockEmail->setValid(false);
        $mockEmail->setLastChecked($date);
        $mockEmail->setInvalidReason('Disposable e-mail addresses are not allowed' . PHP_EOL);
        $this->repository->expects($this->once())
            ->method('saveEmailAddress')
            ->with($mockEmail);

        $this->assertEquals(false, $mock->validateEmailAddress('test@test.com', $date));
    }

    public function testDisposableDomain()
    {
        $date = new \DateTimeImmutable();
        $this->repository->expects($this->once())
            ->method('getDisposableEmailDomain')
            ->with('test.com')
            ->willReturn(null);
        $mock = $this->getMockWithMethods(['getEmailByAddress', 'getConfig']);
        $mock->expects($this->once())
            ->method('getEmailByAddress')
            ->willReturn(null);
        $mock->expects($this->once())
            ->method('getConfig')
            ->willReturn($this->createConfig(true, true, false));

        $mockEmail = new EmailAddress();
        $mockEmail->setEmail('test@test.com');
        $mockEmail->setValid(true);
        $mockEmail->setLastChecked($date);
        $mockEmail->setInvalidReason(null);
        $this->repository->expects($this->once())
            ->method('saveEmailAddress')
            ->with($mockEmail);

        $this->assertEquals(true, $mock->validateEmailAddress('test@test.com', $date));
    }


    private function getMockWithMethods(array $methods = []): MockObject|EmailAddresses
    {
        return $this->getMockBuilder(EmailAddresses::class)->setConstructorArgs([$this->repository, $this->hydrator, $this->validator])->onlyMethods($methods)->getMock();
    }

    private function getClass(): EmailAddresses
    {
        return new EmailAddresses($this->repository, $this->hydrator, $this->validator);
    }

    private function createConfig(bool $dns, bool $disposable, bool $smtp): EmailConfig
    {
        $config = new EmailConfig();
        $config->setCheckDns($dns);
        $config->setCheckDisposableDomain($disposable);
        $config->setCheckSmtp($smtp);
        return $config;
    }
}
