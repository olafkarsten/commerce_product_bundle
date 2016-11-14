<?php

namespace Drupal\commerce_static_bundle;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Static bundle entities.
 *
 * @ingroup commerce_static_bundle
 *
 * @ToDo Replace the LinkGeneratorTrait with \Drupal\Core\Link
 */
class StaticBundleListBuilder extends EntityListBuilder {

  use LinkGeneratorTrait;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Static bundle ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\commerce_static_bundle\Entity\StaticBundle */
    $row['id'] = $entity->id();
    $row['name'] = $this->l(
      $entity->label(),
      new Url(
        'entity.static_bundle.edit_form', array(
          'static_bundle' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }

}
