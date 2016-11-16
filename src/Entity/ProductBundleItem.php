<?php

namespace Drupal\commerce_product_bundle\Entity;

use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Drupal\commerce_product\Form\ProductVariationInlineForm;
use Drupal\commerce_product_bundle\Entity\BundleItemInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the product bundle item entity.
 *
 * @ingroup commerce_product_bundle
 *
 * @ContentEntityType(
 *   id = "commerce_product_bundle_item",
 *   label = @Translation("Product bundle item"),
 *   bundle_label = @Translation("Product bundle item type"),
 *   handlers = {
 *     "storage" = "Drupal\commerce_product_bundle\ProductBundleItemStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\commerce_product_bundle\ProductBundleItemListBuilder",
 *     "views_data" = "Drupal\commerce_product_bundle\Entity\ProductBundleItemViewsData",
 *     "translation" = "Drupal\commerce_product_bundle\ProductBundleItemTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\commerce_product_bundle\Form\ProductBundleItemForm",
 *       "add" = "Drupal\commerce_product_bundle\Form\ProductBundleItemForm",
 *       "edit" = "Drupal\commerce_product_bundle\Form\ProductBundleItemForm",
 *       "delete" = "Drupal\commerce_product_bundle\Form\ProductBundleItemDeleteForm",
 *     },
 *     "access" = "Drupal\commerce_product_bundle\ProductBundleItemAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\commerce_product_bundle\ProductBundleItemHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "commerce_product_bundle_item",
 *   data_table = "commerce_product_bundle_item_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer product bundle item entities",
 *   entity_keys = {
 *     "id" = "bundle_item_id",
 *     "bundle" = "type",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "title",
 *     "uuid" = "uuid",
 *     "uid" = "uid",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/commerce/config/product-bundles/bundle-items/{commerce_product_bundle_item}",
 *     "add-page" = "/admin/commerce/config/product-bundles/bundle-items/add",
 *     "add-form" = "/admin/commerce/config/product-bundles/bundle-items/add/{commerce_product_bundle_item_type}",
 *     "edit-form" = "/admin/commerce/config/product-bundles/bundle-items/{commerce_product_bundle_item}/edit",
 *     "delete-form" = "/admin/commerce/config/product-bundles/bundle-items/{commerce_product_bundle_item}/delete",
 *     "revision" = "/admin/commerce/config/product-bundles/bundle-items/{commerce_product_bundle_item}/revisions/{revision}/view",
 *     "revision_revert" = "/admin/commerce/config/product-bundles/bundle-items/{commerce_product_bundle_item}/revisions/{revision}/revert",
 *     "translation_revert" = "/admin/commerce/config/product-bundles/bundle-items/{commerce_product_bundle_item}/revisions/{revision}/revert/{langcode}",
 *     "revision_delete" = "/admin/commerce/config/product-bundles/bundle-items/{commerce_product_bundle_item}/revisions/{revision}/delete",
 *     "collection" = "/admin/commerce/config/product-bundles/bundle-items",
 *   },
 *   bundle_entity_type = "commerce_product_bundle_item_type",
 *   field_ui_base_route = "entity.commerce_product_bundle_item_type.edit_form"
 * )
 */
class ProductBundleItem extends RevisionableContentEntityBase implements BundleItemInterface {

  use EntityChangedTrait;

  /**
   * The minimum quantity.
   *
   * @var int
   */
  protected $min_quantity = 1;

  /**
   * The minimum quantity.
   *
   * @var int
   */
  protected $max_quantity = 1;

  /**
   * The unit price for one unit of the bundle item
   *
   * @var  \Drupal\commerce_price\Price
   */
  protected $unit_price;

  /**
   * @var \Drupal\commerce_product\Entity\ProductVariationInterface[]
   *   The variations that bundle items
   *    belongs to.
   */
  protected $variations = [];

  /**
   * @var \Drupal\commerce_product\Entity\ProductInterface
   */
  protected $product;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'uid' => \Drupal::currentUser()->id(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Author'))
      ->setDescription(t('The user ID of author of the product bundle item entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\commerce_product_bundle\Entity\ProductBundleItem::getCurrentUserId')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'title'  => 'hidden',
        'type'   => 'author',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type'     => 'entity_reference_autocomplete',
        'weight'   => 5,
        'settings' => array(
          'match_operator'    => 'CONTAINS',
          'size'              => '60',
          'autocomplete_type' => 'tags',
          'placeholder'       => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The title of the product bundle item entity.'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setSettings(array(
        'max_length'      => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label'  => 'hidden',
        'type'   => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type'   => 'string_textfield',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the product bundle item is published.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE);

    // The price is not required because it's not guaranteed to be used
    // for storage. We may use the price of the referenced variations.
    // entity.
    $fields['bundle_item_price'] = BaseFieldDefinition::create('commerce_price')
      ->setLabel(t('The price of a Single Bundle Item'))
      ->setDescription(t('The bundle item price'))
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

    $fields['product_reference'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Product Reference'))
      ->setDescription(t('Reference to a product.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'commerce_product')
      ->setSetting('handler', 'default')
      //@ToDo Not sure wether to set to true or false
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'entity_reference_label',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['min_quantity'] = BaseFieldDefinition::create('decimal')
      ->setLabel(t('Minimum Quantity'))
      ->setDescription(t('The minimum quantity.'))
      ->setSetting('unsigned', TRUE)
      ->setRequired(TRUE)
      ->setDefaultValue(1)
      ->setDisplayOptions('form', [
        'type'   => 'number',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['max_quantity'] = BaseFieldDefinition::create('decimal')
      ->setLabel(t('Maximum Quantity'))
      ->setDescription(t('The maximum quantity.'))
      ->setSetting('unsigned', TRUE)
      ->setRequired(TRUE)
      ->setDefaultValue(1)
      ->setDisplayOptions('form', [
        'type'   => 'number',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_timestamp'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Revision timestamp'))
      ->setDescription(t('The time that the current revision was created.'))
      ->setQueryable(FALSE)
      ->setRevisionable(TRUE);

    $fields['revision_uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Revision user ID'))
      ->setDescription(t('The user ID of the author of the current revision.'))
      ->setSetting('target_type', 'user')
      ->setQueryable(FALSE)
      ->setRevisionable(TRUE);

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

  /**
   * Default value callback for 'uid' base field definition.
   *
   * @see ::baseFieldDefinitions()
   *
   * @return array
   *   An array of default values.
   */
  public static function getCurrentUserId() {
    return [\Drupal::currentUser()->id()];
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

    // If no revision author has been set explicitly, make the product_bundle_item owner the
    // revision author.
    if (!$this->getRevisionAuthor()) {
      $this->setRevisionAuthorId($this->getOwnerId());
    }
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
  public function setOwnerId($uid) {
    $this->set('uid', $uid);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionAuthor() {
    return $this->get('revision_uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionAuthorId($uid) {
    $this->set('revision_uid', $uid);

    return $this;
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
  public function getRevisionCreationTime() {
    return $this->get('revision_timestamp')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionCreationTime($timestamp) {
    $this->set('revision_timestamp', $timestamp);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getStores() {
    // TODO: Proxy the referenced variations.
  }

  /**
   * {@inheritdoc}
   */
  public function getUnitPrice() {
    if (!$this->get('unit_price')->isEmpty()) {
      return $this->get('unit_price')->first()->toPrice();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setUnitPrice(Price $unit_price) {
    $this->set('unit_price', $unit_price);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setQuantity($quantity) {
    $this->setMinimumQuantity($quantity)
            ->setMaximumQuantity($quantity);

    return $this;
  }

  /**
   * @inheritdoc
   */
  public function setMinimumQuantity($minimum_quantity) {
    $this->set('min_quantity', $minimum_quantity);

    return $this;
  }

  /**
   * @inheritdoc
   */
  public function setMaximumQuantity($maximum_quantity) {
    $this->set('max_quantity', $maximum_quantity);
  }

  /**
   * @return mixed
   */
  public function getProductId() {
    return $this->getProduct()->target_id;
  }

  /**
   * Get the referenced product.
   */
  public function getProduct() {
    $product = $this->get('product')->referencedEntities();

    return $product[0];
  }

  /**
   * @inheritdoc
   */
  public function setProduct(ProductInterface $product) {
     $this->set('product', $product);
    return $this;
  }

  /**
   * Gets whether the product has variations.
   *
   * A product must always have at least one variation, but a newly initialized
   * (or invalid) product entity might not have any.
   *
   * @return bool
   *   TRUE if the product has variations, FALSE otherwise.
   */
  public function hasVariations() {
    return !$this->get('variations')->isEmpty();
  }

  /**
   * Adds a variation.
   *
   * @param \Drupal\commerce_product\Entity\ProductVariationInterface $variation
   *   The variation.
   *
   * @return $this
   */
  public function addVariation(ProductVariationInterface $variation) {
    if (!$this->hasVariation($variation)) {
      $this->get('variations')->appendItem($variation);
    }

    return $this;
  }

  /**
   * Checks if the bundle item has a given variation
   *
   * @param ProductVariationInterface $variation
   *
   * @return bool
   */
  public function hasVariation(ProductVariationInterface $variation) {
    return in_array($variation->id(), $this->getVariationIds());
  }

  /**
   * Gets the variation IDs.
   *
   * @return int[]
   *   The variation IDs.
   */
  public function getVariationIds() {
    $variation_ids = [];
    foreach ($this->get('variations') as $field_item) {
      $variation_ids[] = $field_item->target_id;
    }

    return $variation_ids;
  }

  /**
   * Removes a variation.
   *
   * @param \Drupal\commerce_product\Entity\ProductVariationInterface $variation
   *   The variation.
   *
   * @return $this
   */
  public function removeVariation(ProductVariationInterface $variation) {
    $index = $this->getVariationIndex($variation);
    if ($index !== FALSE) {
      $this->get('variations')->offsetUnset($index);
    }

    return $this;
  }

  /**
   * Gets the index of the given variation.
   *
   * @param \Drupal\commerce_product\Entity\ProductVariationInterface $variation
   *   The variation.
   *
   * @return int|bool
   *   The index of the given variation, or FALSE if not found.
   */
  protected function getVariationIndex(ProductVariationInterface $variation) {
    return array_search($variation->id(), $this->getVariationIds());
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultVariation() {
    foreach ($this->getVariations() as $variation) {
      // Return the first active variation.
      if ($variation->isActive()) {
        return $variation;
      }
    }
  }

  /**
   * Gets the variations.
   *
   * @return \Drupal\commerce_product\Entity\ProductVariationInterface[]
   *   The variations.
   */
  public function getVariations() {
    $variations = $this->get('variations')->referencedEntities();

    return $this->ensureTranslations($variations);
  }

  /**
   * Sets the variations.
   *
   * @param \Drupal\commerce_product\Entity\ProductVariationInterface[] $variations
   *   The variations.
   *
   * @return $this
   */
  public function setVariations(array $variations) {
    $this->set('variations', $variations);

    return $this;
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

  public function getMinimumQuantity() {
    return $this->get('min_quantity');
  }

  public function getMaximumQuantity() {
    return $this->get('max_quantity');
  }

}
