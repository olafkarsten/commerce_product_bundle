<?php

namespace Drupal\commerce_product_bundle;

use Drupal\commerce\CommerceContentEntityStorage;
use Drupal\commerce_product_bundle\Entity\BundleInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\MemoryCache\MemoryCacheInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Defines the storage handler class for product bundle item entities.
 *
 * This extends the base storage class, adding required special handling for
 * product bundle item entities.
 *
 * @ingroup commerce_product_bundle
 */
class ProductBundleItemStorage extends CommerceContentEntityStorage implements ProductBundleItemStorageInterface {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a new ProductBundleItemStorage object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection to be used.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend to be used.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Cache\MemoryCache\MemoryCacheInterface $memory_cache
   *   The memory cache.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(
    EntityTypeInterface $entity_type,
    Connection $database,
    EntityFieldManagerInterface $entity_field_manager,
    CacheBackendInterface $cache,
    LanguageManagerInterface $language_manager,
    MemoryCacheInterface $memory_cache,
    EntityTypeBundleInfoInterface $entity_type_bundle_info,
    EntityTypeManagerInterface $entity_type_manager,
    EventDispatcherInterface $event_dispatcher,
    RequestStack $request_stack
  ) {
    parent::__construct($entity_type, $database, $entity_field_manager, $cache, $language_manager, $memory_cache, $entity_type_bundle_info, $entity_type_manager, $event_dispatcher);

    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(
    ContainerInterface $container,
    EntityTypeInterface $entity_type
  ) {
    return new static(
      $entity_type,
      $container->get('database'),
      $container->get('entity_field.manager'),
      $container->get('cache.entity'),
      $container->get('language_manager'),
      $container->get('entity.memory_cache'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity_type.manager'),
      $container->get('event_dispatcher'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function loadFromContext(BundleInterface $product_bundle) {
    if (!$product_bundle->hasBundleItems()) {
      return NULL;
    }

    $current_request = $this->requestStack->getCurrentRequest();

    $bundle_item_ids = $product_bundle->getBundleItemIds();
    $bundle_item_id = $current_request->query->get('v');

    if ($bundle_item_id) {
      if (in_array($bundle_item_id, $bundle_item_ids)) {
        /** @var \Drupal\commerce_product_bundle\Entity\BundleItemInterface */
        $bundle_item = $this->load($bundle_item_id);
        if ($bundle_item->isPublished() && $bundle_item->access('view')) {
          return $bundle_item;
        }
      }
    }

    return $this->load($bundle_item_ids[0]);
  }

  /**
   * {@inheritdoc}
   */
  public function loadEnabled(BundleInterface $product_bundle) {
    $ids = [];
    foreach ($product_bundle->bundle_items as $bundleItem) {
      $ids[$bundleItem->target_id] = $bundleItem->target_id;
    }
    // Speed up loading by filtering out the IDs of disabled bundle items.
    $query = $this->getQuery()
      ->addTag('entity_access')
      ->condition('status', TRUE)
      ->condition('bundle_item_id', $ids, 'IN');
    $result = $query->execute();
    if (empty($result)) {
      return [];
    }
    // Restore the original sort order.
    $result = array_intersect_key($ids, $result);

    /** @var \Drupal\commerce_product_bundle\Entity\BundleItemInterface $enabled_bundle_items */
    $enabled_bundle_items = $this->loadMultiple($result);
    // Filter out variations that can't be accessed.
    foreach ($enabled_bundle_items as $bundle_item_id => $enabled_bundle_item) {
      if (!$enabled_bundle_item->access('view')) {
        unset($enabled_bundle_items[$bundle_item_id]);
      }
    }

    return $enabled_bundle_items;
  }

}
