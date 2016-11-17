<?php

namespace Drupal\commerce_product_bundle;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of product bundle item entities.
 *
 * @ingroup commerce_product_bundle
 */
class ProductBundleItemListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

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
    /* @var $entity \Drupal\commerce_product_bundle\Entity\ProductBundleItem */
    $row['id'] = $entity->id();
    $row['name'] = $this->l(
      $entity->label(),
      new Url(
        'entity.commerce_product_bundle_i.edit_form', array(
          'commerce_product_bundle_i' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }

}
