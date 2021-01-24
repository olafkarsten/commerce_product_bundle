<?php

namespace Drupal\commerce_product_bundle\Entity;

use Drupal\commerce\Entity\CommerceContentEntityBase;
use Drupal\commerce\EntityOwnerTrait;
use Drupal\commerce_price\Price;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the product bundle entity.
 *
 * @ContentEntityType(
 *   id = "commerce_product_bundle",
 *   label = @Translation("Product bundle"),
 *   label_collection = @Translation("Product bundles"),
 *   label_singular = @Translation("product bundle"),
 *   label_plural = @Translation("product bundles"),
 *   label_count = @PluralTranslation(
 *     singular = "@count product bundle",
 *     plural = "@count product bundles",
 *   ),
 *   bundle_label = @Translation("Product bundle type"),
 *   handlers = {
 *     "access" = "\Drupal\entity\EntityAccessControlHandler",
 *     "query_access" = "Drupal\entity\QueryAccess\QueryAccessHandler",
 *     "storage" = "Drupal\commerce_product_bundle\ProductBundleStorage",
 *     "permission_provider" = "Drupal\entity\EntityPermissionProvider",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\commerce_product_bundle\ProductBundleListBuilder",
 *     "views_data" = "Drupal\commerce_product_bundle\ProductBundleViewsData",
 *     "translation" = "Drupal\commerce_product_bundle\ProductBundleTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\commerce_product_bundle\Form\ProductBundleForm",
 *       "add" = "Drupal\commerce_product_bundle\Form\ProductBundleForm",
 *       "edit" = "Drupal\commerce_product_bundle\Form\ProductBundleForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *       "delete-multiple" = "Drupal\entity\Routing\DeleteMultipleRouteProvider",
 *     },
 *    "local_task_provider" = {
 *        "default" = "Drupal\entity\Menu\DefaultEntityLocalTaskProvider",
 *     },
 *   },
 *   base_table = "commerce_product_bundle",
 *   data_table = "commerce_product_bundle_field_data",
 *   fieldable = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer commerce_product_bundle",
 *   permission_granularity = "bundle",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "label" = "title",
 *     "uuid" = "uuid",
 *     "uid" = "uid",
 *     "owner" = "uid",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/product-bundle/{commerce_product_bundle}",
 *     "add-page" = "/product-bundle/add",
 *     "add-form" = "/product-bundle/add/{commerce_product_bundle_type}",
 *     "edit-form" = "/product-bundle/{commerce_product_bundle}/edit",
 *     "delete-form" = "/product-bundle/{commerce_product_bundle}/delete",
 *     "collection" = "/admin/commerce/product-bundles",
 *   },
 *   bundle_entity_type = "commerce_product_bundle_type",
 *   field_ui_base_route = "entity.commerce_product_bundle_type.edit_form"
 * )
 */
class ProductBundle extends CommerceContentEntityBase implements BundleInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;
  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->bundle();
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->get('title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($title) {
    $this->set('title', $title);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setStores(array $stores) {
    $this->set('stores', $stores);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getStores() {
    return $this->get('stores')->referencedEntities();
  }

  /**
   * {@inheritdoc}
   */
  public function getStoreIds() {
    $store_ids = [];
    foreach ($this->get('stores') as $store_item) {
      $store_ids[] = $store_item->target_id;
    }
    return $store_ids;
  }

  /**
   * {@inheritdoc}
   */
  public function setStoreIds(array $store_ids) {
    $this->set('stores', $store_ids);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOrderItemTypeId() {
    // The order item type is a bundle-level setting.
    $type_storage = $this->entityTypeManager()
      ->getStorage('commerce_product_bundle_type');
    $type_entity = $type_storage->load($this->bundle());

    return $type_entity->getOrderItemTypeId();
  }

  /**
   * {@inheritdoc}
   */
  public function getOrderItemTitle() {
    return $this->label();
  }

  /**
   * {@inheritdoc}
   */
  public function setPrice(Price $price) {
    $this->set('bundle_price', $price);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPrice() {
    if ($this->get('bundle_price')->isEmpty()) {
      return NULL;
    }
    return $this->get('bundle_price')->first()->toPrice();
  }

  /**
   * @inheritdoc
   */
  public function getBundleItems() {
    $bundle_items = $this->get('bundle_items')->referencedEntities();

    return $this->ensureTranslations($bundle_items);
  }

  /**
   * @inheritdoc
   */
  public function setBundleItems(array $bundle_items) {
    $this->set('bundle_items', $bundle_items);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addBundleItem(BundleItemInterface $bundle_item) {
    if (!$this->hasBundleItem($bundle_item)) {
      $this->get('bundle_items')->appendItem($bundle_item);
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeBundleItem(BundleItemInterface $bundle_item) {
    $index = $this->getBundleItemIndex($bundle_item);
    if ($index !== FALSE) {
      $this->get('bundle_items')->offsetUnset($index);
    }

    return $this;
  }

  /**
   * Gets the index of the given bundle item.
   *
   * @param \Drupal\commerce_product_bundle\Entity\BundleItemInterface $bundle_item
   *   The bundle item.
   *
   * @return int|bool
   *   The index of the given bundle item, or FALSE if not found.
   */
  protected function getBundleItemIndex(BundleItemInterface $bundle_item) {
    return array_search($bundle_item->id(), $this->getBundleItemIds());
  }

  /**
   * {@inheritdoc}
   */
  public function hasBundleItems() {
    return !$this->get('bundle_items')->isEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function hasBundleItem(BundleItemInterface $bundle_item) {
    return in_array($bundle_item->id(), $this->getBundleItemIds());
  }

  /**
   * {@inheritdoc}
   */
  public function getBundleItemIds() {
    $item_ids = [];
    foreach ($this->get('bundle_items') as $field_item) {
      $item_ids[] = $field_item->target_id;
    }

    return $item_ids;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    // Ensure there's a back-reference on each product bundle item.
    foreach ($this->bundle_items as $item) {
      $item = $item->entity;
      if ($item->bundle_id->isEmpty()) {
        $item->bundle_id = $this->id();
        $item->save();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(
    EntityStorageInterface $storage,
    array $entities
  ) {
    // Delete the product bundle items of a deleted product bundle.
    $bundleItems = [];

    /** @var \Drupal\commerce_product_bundle\Entity\BundleInterface $bundle */
    foreach ($entities as $entity) {
      if (empty($entity->bundle_items)) {
        continue;
      }

      foreach ($entity->bundle_items as $item) {
        $bundleItems[$item->target_id] = $item->entity;
      }
    }

    $storage = \Drupal::service('entity_type.manager')
      ->getStorage('commerce_product_bundle_i');
    $storage->delete($bundleItems);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['stores'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Stores'))
      ->setDescription(t('The product bundle stores.'))
      ->setRequired(TRUE)
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'commerce_store')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('form', [
        'type' => 'commerce_entity_select',
        'weight' => -10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid']
      ->setLabel(t('Author'))
      ->setDescription(t('The author of the product bundle.'))
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The title of the product bundle entity.'))
      ->setSettings([
        'max_length' => 128,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['path'] = BaseFieldDefinition::create('path')
      ->setLabel(t('URL alias'))
      ->setDescription(t('The product bundle URL alias.'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'path',
        'weight' => 30,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setComputed(TRUE);

    // The price is not required because it's not guaranteed to be used
    // for storage. We may use the price of the referenced bundle items.
    $fields['bundle_price'] = BaseFieldDefinition::create('commerce_price')
      ->setLabel(t('Bundle price'))
      ->setDescription(t('The product bundle base price. If set, the prices of the product bundle items will be ignored. Set only, if you want a global price per product bundle, independent from its items.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'commerce_price_default',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'commerce_price_default',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['bundle_items'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Bundle items'))
      ->setDescription(t('The product bundle items.'))
      ->setRequired(FALSE)
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'commerce_product_bundle_i')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'type' => 'commerce_add_to_cart',
        'weight' => 10,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 10,
        'settings' => [
          'override_labels' => TRUE,
          'label_singular' => 'bundle item',
          'label_plural' => 'bundle items',
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
          'match_limit' => 10,
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status']
      ->setLabel(t('Published'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => 90,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public static function bundleFieldDefinitions(
    EntityTypeInterface $entity_type,
    $bundle,
    array $base_field_definitions
  ) {
    /** @var \Drupal\Core\Field\BaseFieldDefinition[] $fields */
    $fields = [];
    $fields['bundle_items'] = clone $base_field_definitions['bundle_items'];
    /** @var \Drupal\commerce_product_bundle\Entity\BundleTypeInterface $product_bundle_type */
    $product_bundle_type = ProductBundleType::load($bundle);
    if ($product_bundle_type) {
      $bundle_item_type_id = $product_bundle_type->getBundleItemTypeId();
      // Restrict the bundle items field to the configured type.
      $fields['bundle_items']->setSetting('handler_settings', [
        'target_bundles' => [$bundle_item_type_id => $bundle_item_type_id],
      ]);
    }

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), [
      'url.query_args:v',
      'store',
    ]);
  }

}
