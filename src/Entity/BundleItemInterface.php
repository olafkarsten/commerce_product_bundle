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
   * Returns the product bundle item published status indicator.
   *
   * Unpublished product bundle item are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the product bundle item is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a product bundle item.
   *
   * @param bool $published
   *   TRUE to set this product bundle item to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   *   The called product bundle item entity.
   */
  public function setPublished($published);

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
   * @return string
   *   The bundle item quantity
   */
  public function getQuantity();


  /**
   * Sets the quantity for the bundle item.
   *
   * @param string $quantity
   *   The bundle item quantity.
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   *   The called product bundle item entity.
   */
  public function setQuantity($quantity);

  /**
   * Sets the minimum quantity of the product variations.
   *
   * @param int $minimum_quantity
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   */
  public function setMinimumQuantity($minimum_quantity);

  /**
   * Gets the minimum quantity of the product variations.
   *
   * @return int
   */
  public function getMinimumQuantity();

  /**
   * Sets the maximum quantity of the product variations
   *
   * @param $minimum_quantity
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   */
  public function setMaximumQuantity($minimum_quantity);

  /**
   * Gets the maximum quantity of the product variations.
   *
   * @return int
   */
  public function getMaximumQuantity();

  /**
   * Get the referenced product.
   *
   * @return null | \Drupal\commerce_product\Entity\ProductInterface
   *    The referenced commerce product.
   */
  public function getProduct();

  /**
   * Set the referenced product.
   *
   * @param \Drupal\commerce_product\Entity\ProductInterface $product
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   */
  public function setProduct(ProductInterface $product);

  /**
   * Sets the variations.
   *
   * @param \Drupal\commerce_product\Entity\ProductVariationInterface[] $variations
   *   The variations.
   *
   * @return $this
   */
  public function setVariations(array $variations);

  /**
   * Get the referenced product variations.
   *
   * @return \Drupal\commerce_product\Entity\ProductVariationInterface[]
   */
  public function getVariations();

  /**
   * Get the default variation.
   *
   * @return \Drupal\commerce_product\Entity\ProductVariationInterface
   */
  public function getDefaultVariation();

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
   * Sets the price of one unit of the referenced
   * product variations
   *
   * @param Price $unit_price
   *
   * @return \Drupal\commerce_product_bundle\Entity\BundleItemInterface
   *   The called product bundle item entity.
   */
  public function setUnitPrice(Price $unit_price);

  /**
   * Gets the price of one unit of the referenced
   * product variations.
   *
   * @return Price $unit_price
   */
  public function getUnitPrice();

  /**
   * Check wether the bundleItem has an own unit price.
   *
   * @return bool
   */
  public function hasUnitPrice();

}
