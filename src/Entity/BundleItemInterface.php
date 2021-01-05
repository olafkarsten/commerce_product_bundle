<?php

namespace Drupal\commerce_product_bundle\Entity;

use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining product bundle item entities.
 *
 * @ingroup commerce_product_bundle
 */
interface BundleItemInterface extends EntityChangedInterface, EntityOwnerInterface, EntityPublishedInterface {

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
   * Set whether the product bundle item is required or not.
   *
   * @param bool $required
   *   Set TRUE if required, FALSE if optional.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   *   The called product bundle item entity.
   */
  public function setRequired($required);

  /**
   * Whether the product bundle item is required or not.
   *
   * @return bool
   *   TRUE if required, FALSE if optional.
   */
  public function isRequired();

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
   * Gets whether the bundle item has a product set or not.
   *
   * @return bool
   *   TRUE if the bundle item contains a product reference. FALSE otherwise.
   */
  public function hasProduct();

  /**
   * Get the referenced product.
   *
   * @return null|\Drupal\commerce_product\Entity\ProductInterface
   *   The referenced commerce product or null
   *    if no product is referenced.
   */
  public function getProduct();

  /**
   * Gets the bundle item's product id.
   *
   * @return string|int
   *   The bundle item's product id.
   */
  public function getProductId();

  /**
   * Set the referenced product.
   *
   * If a new product is referenced, the variations references will be
   * resetted.
   *
   * @param \Drupal\commerce_product\Entity\ProductInterface $product
   *   The product.
   *
   * @return $this
   */
  public function setProduct(ProductInterface $product);

  /**
   * Sets the variations.
   *
   * If the bundle item doesn't hold a product reference yet, the product of the
   * first variation will be set as the bundle items product.
   *
   * @param \Drupal\commerce_product\Entity\ProductVariationInterface[] $variations
   *   The variations.
   *
   * @throws \InvalidArgumentException
   *    In case the variations don't belong to the same product or if applicable to
   *    the already referenced product.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   *   The called product bundle item entity.
   */
  public function setVariations(array $variations);

  /**
   * Gets whether the bundle item has restricted variations.
   *
   * @return bool
   *   TRUE if the bundle item has restricted available variations, FALSE otherwise.
   */
  public function hasVariations();

  /**
   * Gets the product variations limited by the bundle item or enabled on the product.
   *
   * This method returns the variations, if any, specified (limited) by
   * the bundle item, or fall back to all enabled variations of the referenced product.
   *
   * @return \Drupal\commerce_product\Entity\ProductVariationInterface[]
   *   The product variations.
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
   *   The default product variation.
   */
  public function getDefaultVariation();

  /**
   * Adds a variation.
   *
   * If the bundle item does not yet hold a product reference, nothing happens.
   * So if you unsure you should propably check for existence of the product
   * reference.
   *
   * If the bundle item has a product reference, but doesn't restrict
   * the variations, nothing will happen. You can check for this
   * with hasVariations().
   *
   * @code
   * if($bundleItem->hasProduct() && $bundleItem->hasVariations()){
   *    $bundleItem->addVariation($variation)
   * } else {
   *    $bundleItem->setVariations([$variation]);
   * }
   * @endcode
   *
   * @param \Drupal\commerce_product\Entity\ProductVariationInterface $variation
   *   The variation.
   *
   * @throws \InvalidArgumentException
   *    In case the variation don't belong to the referenced product.
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
   * @throws \InvalidArgumentException
   *   In case the variation passed as argument is not referenced by the bundle
   *   item.
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
   * @return \Drupal\commerce_price\Price|null
   *   The unit price or NULL.
   */
  public function getUnitPrice();

  /**
   * Check whether the bundleItem has an own unit price.
   *
   * @return bool
   *   True it the bundle item has an own unit price set,
   *   false if not.
   */
  public function hasUnitPrice();

}
