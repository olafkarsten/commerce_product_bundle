<?php

namespace Drupal\commerce_product_bundle;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of product bundle entities.
 *
 * @ingroup commerce_product_bundle
 *
 * @ToDo Replace the LinkGeneratorTrait with \Drupal\Core\Link
 */
class ProductBundleListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Product bundle ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\commerce_product_bundle\Entity\ProductBundle */
    $row['id'] = $entity->id();
    $row['name'] = Link::fromTextAndUrl(
      $entity->label(),
      new Url(
        'entity.commerce_product_bundle.edit_form', [
          'commerce_product_bundle' => $entity->id(),
        ]
      )
    );
    return $row + parent::buildRow($entity);
  }

}
