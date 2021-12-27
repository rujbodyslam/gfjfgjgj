<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Command;

use Magento\AuthorizenetAcceptjs\Gateway\Command\AcceptPaymentStrategyCommand;
use Magento\AuthorizenetAcceptjs\Gateway\Command\RefundTransactionStrategyCommand;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Command\ResultInterface;
use Magento\Payment\Gateway\CommandInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\AuthorizenetAcceptjs\Gateway\Command\AcceptPaymentStrategyCommandTest
 */
class AcceptPaymentStrategyCommandTest extends TestCase
{
    /**
     * @var CommandInterface|MockObject
     */
    private $commandMock;

    /**
     * @var CommandInterface|MockObject
     */
    private $transactionDetailsCommandMock;

    /**
     * @var CommandPoolInterface|MockObject
     */
    private $commandPoolMock;

    /**
     * @var RefundTransactionStrategyCommand
     */
    private $command;

    /**
     * @var ResultInterface|MockObject
     */
    private $transactionResultMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->transactionDetailsCommandMock = $this->createMock(CommandInterface::class);
        $this->commandMock = $this->createMock(CommandInterface::class);
        $this->transactionResultMock = $this->createMock(ResultInterface::class);
        $this->commandPoolMock = $this->createMock(CommandPoolInterface::class);
        $this->command = $objectManagerHelper->getObject(
            AcceptPaymentStrategyCommand::class,
            [
                'commandPool' => $this->commandPoolMock,
                'subjectReader' => new SubjectReader(),
            ]
        );
    }

    /**
     * @param string $status
     * @dataProvider inReviewStatusesProvider
     *
     * @return void
     */
    public function testCommandWillAcceptInTheGatewayWhenInFDSReview(string $status)
    {
        // Assert command is executed
        $this->commandMock->expects($this->once())
            ->method('execute');

        $this->commandPoolMock->method('get')
            ->willReturnMap([
                ['get_transaction_details', $this->transactionDetailsCommandMock],
                ['accept_fds', $this->commandMock],
            ]);

        $this->transactionResultMock->method('get')
            ->willReturn([
                'transaction' => [
                    'transactionStatus' => $status,
                ],
            ]);

        $buildSubject = [
            'foo' => '123',
        ];

        $this->transactionDetailsCommandMock->expects($this->once())
            ->method('execute')
            ->with($buildSubject)
            ->willReturn($this->transactionResultMock);

        $this->command->execute($buildSubject);
    }

    /**
     * @return void
     */
    public function testCommandWillDoNothingWhenTransactionHasAlreadyBeenAuthorized()
    {
        // Assert command is never executed
        $this->commandMock->expects($this->never())
            ->method('execute');

        $this->commandPoolMock->method('get')
            ->willReturnMap([
                ['get_transaction_details', $this->transactionDetailsCommandMock],
            ]);

        $this->transactionResultMock->method('get')
            ->willReturn([
                'transaction' => [
                    'transactionStatus' => 'anythingelseisfine',
                ],
            ]);

        $buildSubject = [
            'foo' => '123',
        ];

        $this->transactionDetailsCommandMock->expects($this->once())
            ->method('execute')
            ->with($buildSubject)
            ->willReturn($this->transactionResultMock);

        $this->command->execute($buildSubject);
    }

    /**
     * @return array
     */
    public function inReviewStatusesProvider(): array
    {
        return [
            ['FDSPendingReview'],
            ['FDSAuthorizedPendingReview'],
        ];
    }
}
