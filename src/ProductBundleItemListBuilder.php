<?php

namespace Drupal\commerce_product_bundle;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;
;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of product bundle item entities.
 *
 * @ingroup commerce_product_bundle
 */
class ProductBundleItemListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\commerce_product_bundle\Entity\ProductBundleItem $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::fromTextAndUrl(
      $entity->label(),
      new Url(
        'entity.commerce_product_bundle_i.edit_form', [
          'commerce_product_bundle_i' => $entity->id(),
        ]
      )
    );
    return $row + parent::buildRow($entity);
  }

}
