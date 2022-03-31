<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace Tests\BitBag\SyliusAgreementPlugin\Unit\Resolver\AgreementHistory;

use BitBag\SyliusAgreementPlugin\Entity\Agreement\AgreementHistoryInterface;
use BitBag\SyliusAgreementPlugin\Entity\Agreement\AgreementInterface;
use BitBag\SyliusAgreementPlugin\Repository\AgreementHistoryRepositoryInterface;
use BitBag\SyliusAgreementPlugin\Resolver\AgreementHistory\OrderAgreementHistoryResolver;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Order\Context\CartContext;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Core\Model\OrderInterface;

final class OrderAgreementHistoryResolverTest extends TestCase
{
    /**
     * @dataProvider resolveHistoryDataProvider
     */
    public function test_it_resolves_history_correctly(?int $cartId = null, ?int $agreementId = null, ?object $agreementHistory = null, ?object $output = null): void
    {
        $agreement = $this->createMock(AgreementInterface::class);
        $agreement
            ->expects(self::once())
            ->method('getId')
            ->willReturn($agreementId);

        $cart = $this->createMock(OrderInterface::class);
        $cart->expects(self::atMost(1))
            ->method('getId')
            ->willReturn($cartId);

        $cartContext = $this->createMock(CartContextInterface::class);
        $cartContext
            ->expects(self::once())
            ->method('getCart')
            ->willReturn($cart);

        $repository = $this->createMock(AgreementHistoryRepositoryInterface::class);
        $repository
            ->method('findOneForOrder')
            ->with($agreement, $cart)
            ->willReturn($agreementHistory);

        $subject = new OrderAgreementHistoryResolver($cartContext, $repository);
        self::assertEquals($output, $subject->resolveHistory($agreement));
    }

    public function test_it_supports_order_interface()
    {
        $cartContext = $this->createMock(CartContextInterface::class);

        $cartContext
            ->expects(self::once())
            ->method('getCart')
            ->willReturn(new Order());

        $agreementInterface = $this->createMock(AgreementInterface::class);
        $agreementHistoryRepository = $this->createMock(AgreementHistoryRepositoryInterface::class);
        $orderAgreementHistoryResolver = new OrderAgreementHistoryResolver($cartContext,$agreementHistoryRepository);

        Assert::assertSame($orderAgreementHistoryResolver->supports($agreementInterface),true);
    }

    public function resolveHistoryDataProvider(): array
    {
        $agreementHistory = $this->createMock(AgreementHistoryInterface::class);
        return [
            [1, 2, $agreementHistory, $agreementHistory],
            [1, null, $agreementHistory, null],
            [null, null, $agreementHistory, null],
            [null, null, $agreementHistory, null],
            [null, 1, $agreementHistory, null],
            [null, 1, null, null],
        ];
    }
}