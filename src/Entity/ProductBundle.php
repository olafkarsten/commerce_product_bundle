<?php

namespace Drupal\commerce_product_bundle\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\commerce_price\Price;
use Drupal\user\UserInterface;

/**
 * Defines the product bundle entity.
 *
 * @ingroup commerce_product_bundle
 *
 * @ContentEntityType(
 *   id = "commerce_product_bundle",
 *   label = @Translation("Product bundle"),
 *   label_collection = @Translation("Product bundles"),
 *   label_singular = @Translation("Product bundle"),
 *   label_plural = @Translation("Product bundles"),
 *   label_count = @PluralTranslation(
 *     singular = "@count product bundle",
 *     plural = "@count product bundles",
 *   ),
 *   bundle_label = @Translation("Product bundle type"),
 *   handlers = {
 *     "access" = "Drupal\commerce_product_bundle\ProductBundleAccessControlHandler",
 *     "storage" = "Drupal\commerce_product_bundle\ProductBundleStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\commerce_product_bundle\ProductBundleListBuilder",
 *     "views_data" = "Drupal\commerce_product_bundle\Entity\ProductBundleViewsData",
 *     "translation" = "Drupal\commerce_product\ProductBundleTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\commerce_product_bundle\Form\ProductBundleForm",
 *       "add" = "Drupal\commerce_product_bundle\Form\ProductBundleForm",
 *       "edit" = "Drupal\commerce_product_bundle\Form\ProductBundleForm",
 *       "delete" = "Drupal\commerce_product_bundle\Form\ProductBundleDeleteForm",
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *       "delete-multiple" = "Drupal\entity\Routing\DeleteMultipleRouteProvider",
 *     },
 *     "permission_provider" = "Drupal\commerce_product_bundle\EntityPermissionProvider",
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
 *     "langcode" = "langcode",
 *     "status" = "status",
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
class ProductBundle extends ContentEntityBase implements BundleInterface {

  use EntityChangedTrait;

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
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
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
  public function getOrderItemTypeId() {
    // The order item type is a bundle-level setting.
    $type_storage = $this->entityTypeManager()->getStorage('commerce_product_bundle_type');
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
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'uid' => \Drupal::currentUser()->id(),
    ];
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
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
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

    $storage = \Drupal::service('entity_type.manager')->getStorage('commerce_product_bundle_i');
    $storage->delete($bundleItems);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Author'))
      ->setDescription(t('The user ID of author of the product bundle entity.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
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

    // The price is not required because it's not guaranteed to be used
    // for storage. We may use the price of the referenced bundle items.
    $fields['bundle_price'] = BaseFieldDefinition::create('commerce_price')
      ->setLabel(t('Bundle price'))
      ->setDescription(t('The product bundle base price. If set, the prices of the product bundle items will be ignored. Set only, if you want a global price per product bundle, independent from its items.'))
      ->setDisplayOptions('view', [
        'label'  => 'above',
        'type'   => 'commerce_price_default',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type'   => 'commerce_price_default',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the product bundle is published.'))
      ->setDefaultValue(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

  /**
   * Ensures that the provided entities are in the current entity's language.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface[] $entities
   *   The entities to process.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface[]
   *   The processed entities.
   */
  protected function ensureTranslations(array $entities) {
    $langcode = $this->language()->getId();
    foreach ($entities as $index => $entity) {
      /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
      if ($entity->hasTranslation($langcode)) {
        $entities[$index] = $entity->getTranslation($langcode);
      }
    }

    return $entities;
  }

}
