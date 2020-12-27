<?php

namespace Drupal\Tests\commerce_product_bundle\Kernel\Resolver;

use Drupal\commerce\Context;
use Drupal\commerce_product_bundle\Resolver\BundlePriceResolver;
use Drupal\Tests\commerce_product_bundle\Kernel\CommerceProductBundleKernelTestBase;
use Drupal\commerce_price\Price;

/**
 * Tests the bundle price resolver.
 *
 * @coversDefaultClass \Drupal\commerce_product_bundle\Resolver\BundlePriceResolver
 *
 * @group commerce_product_bundle
 */
class BundlePriceResolverTest extends CommerceProductBundleKernelTestBase {

  /**
   * Tests price revolving.
   *
   * ::covers resolve.
   */
  public function testResolves() {

    $store = $this->prophesize('\Drupal\commerce_store\Entity\StoreInterface');
    $store->getDefaultCurrencyCode()->willReturn('EUR');
    $store = $store->reveal();

    $currentStore = $this->prophesize('Drupal\commerce_store\CurrentStoreInterface');
    $currentStore->getStore()->willReturn($store);

    $notOurEntity = $this->prophesize('Drupal\commerce_product\Entity\ProductVariation');
    $context = new Context($this->user, $store);

    $resolver = new BundlePriceResolver($currentStore->reveal());
    self::assertNull($resolver->resolve($notOurEntity->reveal(), 1, $context));

    $bundle = $this->prophesize('Drupal\commerce_product_bundle\Entity\BundleInterface');
    $bundle->getPrice()->willReturn(new Price('0.00', 'USD'))->shouldBeCalledTimes(1);
    self::assertEquals(new Price('0.00', 'USD'), $resolver->resolve($bundle->reveal(), 1, $context));

    // Wether the getPrice() method gets called.
    $bundle->checkProphecyMethodsPredictions();

    $bundle = $this->prophesize('Drupal\commerce_product_bundle\Entity\BundleInterface');
    $bundle->getPrice()->willReturn(new Price('5.55', 'USD'))->shouldBeCalledTimes(1);
    self::assertEquals(new Price('5.55', 'USD'), $resolver->resolve($bundle->reveal(), 1, $context));

    $bundle = $this->prophesize('Drupal\commerce_product_bundle\Entity\BundleInterface');
    $bundle->getPrice()->willReturn(NULL);
    $bundleItem = $this->prophesize('Drupal\commerce_product_bundle\Entity\BundleItemInterface');
    $bundleItem->getUnitPrice()->willReturn(new Price('11.11', 'EUR'))->shouldBeCalledTimes(5);
    $items = array_fill(0, 5, $bundleItem->reveal());
    $bundle->getBundleItems()->willReturn($items)->shouldBeCalled();
    self::assertEquals(new Price('55.55', 'EUR'), $resolver->resolve($bundle->reveal(), 1, $context));

    $bundle->checkProphecyMethodsPredictions();
  }

  /**
   * Wether the service gets collected by the chain price resolver.
   */
  public function testServiceIsRegistered() {
    /** @var \Drupal\commerce_price/Resolver/ChainPriceResolver $chainPriceResolver */
    $chainPriceResolver = \Drupal::service('commerce_price.chain_price_resolver');
    $resolvers = $chainPriceResolver->getResolvers();
    self::assertContains('bundle_price_resolver', array_keys($resolvers));
  }

}
