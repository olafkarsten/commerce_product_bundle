<?php

namespace Drupal\commerce_product_bundle\Resolver;

use Drupal\commerce\Context;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_price\Resolver\PriceResolverInterface;
use Drupal\commerce_product_bundle\Entity\BundleInterface;
use Drupal\commerce_store\CurrentStoreInterface;

/**
 * Commerce Product Bundle Price Resolver.
 *
 * This checks if a product bundle entity has an own static price. Otherwise
 * it calculates the bundle price from the referenced bundle items.
 */
class BundlePriceResolver implements PriceResolverInterface {

  /**
   * The current store.
   *
   * @var \Drupal\commerce_store\CurrentStoreInterface
   */
  protected $currentStore;

  /**
   * Constructs a new BundlePriceResolver object.
   *
   * @param \Drupal\commerce_store\CurrentStoreInterface $current_store
   *   The current store.
   */
  public function __construct(CurrentStoreInterface $current_store) {
    $this->currentStore = $current_store;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve(PurchasableEntityInterface $entity, $quantity, Context $context) {
    // We operate on product bundles only. Return fast if we have nothing to do.
    if (!$entity instanceof BundleInterface) {
      return NULL;
    }

    // In case the product bundle has a static price, we return that price.
    // Otherwise we compute a dynamic price from the bundle items.
    $price = $entity->getPrice();
    if (!is_null($price)) {
      return $price;
    }
    else {
      $currency_code = $this->currentStore->getStore()->getDefaultCurrencyCode();
      $bundle_price = new Price('0.00', $currency_code);
      foreach ($entity->getBundleItems() as $item) {
        $bundle_price = $bundle_price->add($item->getUnitPrice());
      }
      return $bundle_price;
    }
  }

}
