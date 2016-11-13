<?php

namespace Drupal\commerce_static_bundle;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Static bundle item entities.
 *
 * @ingroup commerce_static_bundle
 */
class StaticBundleItemListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Static bundle item ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\commerce_static_bundle\Entity\StaticBundleItem */
    $row['id'] = $entity->id();
    $row['name'] = $this->l(
      $entity->label(),
      new Url(
        'entity.static_bundle_item.edit_form', array(
          'static_bundle_item' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }

}
