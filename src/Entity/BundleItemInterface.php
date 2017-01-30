<?php

namespace Drupal\commerce_product_bundle\Entity;

use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining product bundle item entities.
 *
 * @ingroup commerce_product_bundle
 */
interface BundleItemInterface extends EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the product bundle item type.
   *
   * @return string
   *   The product bundle item type.
   */
  public function getType();

  /**
   * Gets the product bundle item title.
   *
   * @return string
   *   Title of the product bundle item.
   */
  public function getTitle();

  /**
   * Sets the product bundle item title.
   *
   * @param string $title
   *   The product bundle item title.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   *   The called product bundle item entity.
   */
  public function setTitle($title);

  /**
   * Gets the product bundle item creation timestamp.
   *
   * @return int
   *   Creation timestamp of the product bundle item.
   */
  public function getCreatedTime();

  /**
   * Sets the product bundle item creation timestamp.
   *
   * @param int $timestamp
   *   The product bundle item creation timestamp.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   *   The called product bundle item entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the product bundle item active status indicator.
   *
   * Unactivated product bundle item are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the product bundle item is active.
   */
  public function isActive();

  /**
   * Sets the active status of a product bundle item.
   *
   * @param bool $active
   *   TRUE to set this product bundle item to activate, FALSE to set it to unactivated.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   *   The called product bundle item entity.
   */
  public function setActive($active);

  /**
   * Gets the parent bundle entity.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleInterface
   *   The product bundle entity, or null.
   */
  public function getBundle();

  /**
   * Gets the parent product bundle ID.
   *
   * @return int
   *   The product bundle ID, or null.
   */
  public function getBundleId();

  /**
   * Gets the bundle item quantity.
   *
   * @return float
   *   The bundle item quantity
   */
  public function getQuantity();

  /**
   * Sets the quantity for the bundle item.
   *
   * @param float $quantity
   *   The bundle item quantity.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   *   The called product bundle item entity.
   */
  public function setQuantity($quantity);

  /**
   * Sets the minimum quantity of the product variations.
   *
   * @param float $minimum_quantity
   *   The minimum quantity.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   *   The called product bundle item entity.
   */
  public function setMinimumQuantity($minimum_quantity);

  /**
   * Gets the minimum quantity of the product variations.
   *
   * @return float
   *   The minimum quantity.
   */
  public function getMinimumQuantity();

  /**
   * Sets the maximum quantity of the product variations.
   *
   * @param float $maximum_quantity
   *   The maximum quantity.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   *   The called product bundle item entity.
   */
  public function setMaximumQuantity($maximum_quantity);

  /**
   * Gets the maximum quantity of the product variations.
   *
   * @return float
   *   The maximum quantity.
   */
  public function getMaximumQuantity();

  /**
   * Gets the bundle item's product id.
   *
   * @return string|int
   *   The bundle item's product id.
   */
  public function getProductId();

  /**
   * Get the referenced product.
   *
   * @return null|\Drupal\commerce_product\Entity\ProductInterface
   *    The referenced commerce product or null
   *    if no product is referenced.
   */
  public function getProduct();

  /**
   * Set the referenced product.
   *
   * @param \Drupal\commerce_product\Entity\ProductInterface $product
   *    The product.
   *
   * @return $this
   */
  public function setProduct(ProductInterface $product);

  /**
   * Sets the variations.
   *
   * @param \Drupal\commerce_product\Entity\ProductVariationInterface[] $variations
   *   The variations.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   *   The called product bundle item entity.
   */
  public function setVariations(array $variations);

  /**
   * Gets whether the bundle item has restricted variations.
   *
   * @todo Consider how this may change.
   * @see https://www.drupal.org/node/2837499
   *
   * @return bool
   *   TRUE if the bundle item has restricted available variations, FALSE otherwise.
   */
  public function hasVariations();

  /**
   * Gets the product variations limited by the bundle item or enabled on the product.
   *
   * This method should return the variations, if any, specified (limited) by
   * the bundle item, or fall back to all active variations of the referenced product.
   *
   * @todo: What to do about [limited] variations that are not enabled on their products?
   * @see https://www.drupal.org/node/2837499
   *
   * @return \Drupal\commerce_product\Entity\ProductVariationInterface[]
   *    Array of product variations.
   */
  public function getVariations();

  /**
   * Gets the variation IDs.
   *
   * @return int[]
   *   The variation IDs.
   */
  public function getVariationIds();

  /**
   * Get the default variation.
   *
   * @return \Drupal\commerce_product\Entity\ProductVariationInterface
   *    The default product variation.
   */
  public function getDefaultVariation();

  /**
   * Checks if the bundle item has a given variation.
   *
   * @param \Drupal\commerce_product\Entity\ProductVariationInterface $variation
   *   The product variation.
   *
   * @return bool
   *   True if the bundle item has this product variation
   *    referenced, false if not.
   */
  public function hasVariation(ProductVariationInterface $variation);

  /**
   * Gets the index of the given variation.
   *
   * @param \Drupal\commerce_product\Entity\ProductVariationInterface $variation
   *   The variation.
   *
   * @return int|bool
   *   The index of the given variation, or FALSE if not found.
   */
  public function getVariationIndex(ProductVariationInterface $variation);

  /**
   * Adds a variation.
   *
   * @param \Drupal\commerce_product\Entity\ProductVariationInterface $variation
   *   The variation.
   *
   * @return $this
   */
  public function addVariation(ProductVariationInterface $variation);

  /**
   * Removes a variation.
   *
   * @param \Drupal\commerce_product\Entity\ProductVariationInterface $variation
   *   The variation.
   *
   * @return $this
   */
  public function removeVariation(ProductVariationInterface $variation);

  /**
   * Gets the currently selected variation, or the default variation.
   *
   * @return \Drupal\commerce_product\Entity\ProductVariationInterface
   *   The variation.
   */
  public function getCurrentVariation();

  /**
   * Gets the currently selected variation, or the default variation.
   *
   * @param \Drupal\commerce_product\Entity\ProductVariationInterface $variation
   *   The variation.
   *
   * @return $this
   */
  public function setCurrentVariation(ProductVariationInterface $variation);

  /**
   * Sets the price of one unit of the referenced
   * product variations.
   *
   * @param \Drupal\commerce_price\Price $unit_price
   *   The unit price.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   *   The called product bundle item entity.
   */
  public function setUnitPrice(Price $unit_price);

  /**
   * Gets the price of one unit of the referenced
   * product variations.
   *
   * @return \Drupal\commerce_price\Price
   *   The unit price.
   */
  public function getUnitPrice();

  /**
   * Check wether the bundleItem has an own unit price.
   *
   * @return bool
   *   True it the bundle item has an own unit price set,
   *   false if not.
   */
  public function hasUnitPrice();

}
