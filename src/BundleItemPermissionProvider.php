<?php

namespace Drupal\commerce_product_bundle;

use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\entity\EntityPermissionProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides Commerce Product Bundle Item entity permissions.
 *
 * @see \Drupal\entity\EntityPermissionProvider
 */
class BundleItemPermissionProvider implements EntityPermissionProviderInterface, EntityHandlerInterface {

  use StringTranslationTrait;

  /**
   * The entity type bundle info.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * Constructs a new BundleItemPermissionProvider object.
   *
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info.
   */
  public function __construct(EntityTypeBundleInfoInterface $entity_type_bundle_info) {
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildPermissions(EntityTypeInterface $entity_type) {
    $entity_type_id = $entity_type->id();
    $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type_id);
    $permissions = [];
    foreach ($bundles as $bundle_name => $bundle_info) {
      // The title is in a different format than the product bundle type permissions,
      // to differentiate product bundle types from product bundle item types.
      $permissions["manage {$bundle_name} {$entity_type_id}"] = [
        'title' => $this->t('[Product bundle items] manage %bundle', [
          '%bundle' => $bundle_info['label'],
        ]),
        'provider' => 'commerce_product_bundle',
      ];
    }

    return $permissions;
  }

}
