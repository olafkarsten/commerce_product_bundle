<?php

namespace Drupal\commerce_product_bundle;

use Drupal\commerce_product_bundle\Entity\ProductBundleType;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of product bundle entities.
 *
 * @ingroup commerce_product_bundle
 *
 * @todo Replace the LinkGeneratorTrait with \Drupal\Core\Link
 */
class ProductBundleListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['title'] = $this->t('Title');
    $header['type'] = $this->t('Type');
    $header['status'] = t('Status');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\commerce_product_bundle\Entity\ProductBundle $entity */
    $product_bundle_type = ProductBundleType::load($entity->bundle());
    $row['title']['data'] = [
      '#type' => 'link',
      '#title' => $entity->label(),
    ] + $entity->toUrl()->toRenderArray();
    $row['type'] = $product_bundle_type->label();
    $row['status'] = $entity->isPublished() ? $this->t('Published') : $this->t('Unpublished');
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);

    $bundle_items_url = new Url('entity.commerce_product_bundle_i.collection', [
      'commerce_product_bundle' => $entity->id(),
    ]);
    if ($bundle_items_url->access()) {
      $operations['bundle_items'] = [
        'title' => $this->t('Bundle items'),
        'weight' => 20,
        'url' => $bundle_items_url,
        // Remove the generated destination query parameter, which by default
        // brings the user back to the bundle listing. This behavior would
        // not make sense on the bundle items tab (e.g. re-ordering bundle items
        // should not send the user back to the bundle listing).
        'query' => ['destination' => NULL],
      ];
    }

    return $operations;
  }

}
