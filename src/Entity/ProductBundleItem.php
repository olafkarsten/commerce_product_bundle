<?php

namespace Drupal\commerce_product_bundle\Entity;

use Drupal\commerce\Entity\CommerceContentEntityBase;
use Drupal\commerce\EntityOwnerTrait;
use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\commerce_product\Entity\ProductVariationInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Url;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

/**
 * Defines the product bundle item entity.
 *
 * @ingroup commerce_product_bundle
 *
 * @ContentEntityType(
 *   id = "commerce_product_bundle_i",
 *   label = @Translation("Product bundle item"),
 *   label_collection = @Translation("Product bundle items"),
 *   label_singular = @Translation("Product bundle item"),
 *   label_plural = @Translation("Product bundle items"),
 *   label_count = @PluralTranslation(
 *     singular = "@count product bundle item",
 *     plural = "@count product bundle items",
 *   ),
 *   bundle_label = @Translation("Product bundle item type"),
 *   handlers = {
 *     "access" = "Drupal\commerce_product_bundle\BundleItemAccessControlHandler",
 *     "permission_provider" = "Drupal\commerce_product_bundle\BundleItemPermissionProvider",
 *     "storage" = "Drupal\commerce_product_bundle\ProductBundleItemStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\commerce_product_bundle\ProductBundleItemListBuilder",
 *     "views_data" = "Drupal\commerce_product_bundle\Entity\ProductBundleItemViewsData",
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\commerce_product_bundle\Form\ProductBundleItemForm",
 *       "add" = "Drupal\commerce_product_bundle\Form\ProductBundleItemForm",
 *       "edit" = "Drupal\commerce_product_bundle\Form\ProductBundleItemForm",
 *       "duplicate" = "Drupal\commerce_product_bundle\Form\ProductBundleItemForm",
 *       "delete" = "Drupal\commerce_product_bundle\Form\ProductBundleItemDeleteForm",
 *     },
 *    "local_task_provider" = {
 *       "default" = "Drupal\entity\Menu\DefaultEntityLocalTaskProvider",
 *     },
 *     "inline_form" = "Drupal\commerce_product_bundle\Form\ProductBundleItemInlineForm",
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler",
 *     "route_provider" = {
 *       "default" = "Drupal\commerce_product_bundle\ProductBundleItemRouteProvider",
 *       "delete-multiple" = "Drupal\entity\Routing\DeleteMultipleRouteProvider",
 *     },
 *   },
 *   base_table = "commerce_product_bundle_i",
 *   data_table = "commerce_product_bundle_i_field_data",
 *   fieldable = TRUE,
 *   translatable = TRUE,
 *   translation = {
 *     "content_translation" = {
 *       "access_callback" = "content_translation_translate_access"
 *     },
 *   },
 *   admin_permission = "administer commerce_product_bundle",
 *   entity_keys = {
 *     "id" = "bundle_item_id",
 *     "bundle" = "type",
 *     "label" = "title",
 *     "uuid" = "uuid",
 *     "uid" = "uid",
 *     "owner" = "uid",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "add-form" = "/product-bundle/{commerce_product_bundle}/bundle-items/add",
 *     "edit-form" = "/product-bundle/{commerce_product_bundle}/bundle-items/{commerce_product_bundle_i}/edit",
 *     "duplicate-form" = "/product-bundle/{commerce_product_bundle}/bundle-items/{commerce_product_bundle_i}/duplicate",
 *     "delete-form" = "/product-bundle/{commerce_product_bundle}/bundle-items/{commerce_product_bundle_i}/delete",
 *     "collection" = "/product-bundle/{commerce_product_bundle}/bundle-items",
 *     "drupal:content-translation-overview" = "/product-bundle/{commerce_product_bundle}/bundle-items/{commerce_product_bundle_i}/translations",
 *     "drupal:content-translation-add" = "/product-bundle/{commerce_product_bundle}/bundle-items/{commerce_product_bundle_i}/translations/add/{source}/{target}",
 *     "drupal:content-translation-edit" = "/product-bundle/{commerce_product_bundle}/bundle-items/{commerce_product_bundle_i}/translations/edit/{language}",
 *     "drupal:content-translation-delete" = "/product-bundle/{commerce_product_bundle}/bundle-items/{commerce_product_bundle_i}/translations/delete/{language}",
 *   },
 *   constraints = {
 *     "MinQtyLessThanOrEqualMaxQty" = {}
 *   },
 *   bundle_entity_type = "commerce_product_bundle_i_type",
 *   field_ui_base_route = "entity.commerce_product_bundle_i_type.edit_form"
 * )
 */
class ProductBundleItem extends CommerceContentEntityBase implements BundleItemInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;
  use EntityPublishedTrait;

  /**
   * The parent product bundle.
   *
   * @var \Drupal\commerce_product_bundle\Entity\BundleInterface
   */
  protected $bundle;

  /**
   * The bundle items current active quantity . In case
   * of a fresh bundle, that is the default quantity.
   *
   * @var float
   */
  protected $activeQuantity;

  /**
   * The currently selected variation.
   *
   * @var \Drupal\commerce_product\Entity\ProductVariationInterface
   */
  protected $currentVariation;

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);
    $uri_route_parameters['commerce_product_bundle'] = $this->getBundleId();
    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function toUrl($rel = 'canonical', array $options = []) {
    // Product bundle item URLs depend on the parent product bundle.
    if (!$this->getBundleId()) {
      // RouteNotFoundException tells EntityBase::uriRelationships()
      // to skip this product bundle items link relationships.
      throw new RouteNotFoundException();
    }

    // StringFormatter assumes 'revision' is always a valid link template.
    if (in_array($rel, ['canonical', 'revision'])) {
      $route_name = 'entity.commerce_product_bundle.canonical';
      $route_parameters = [
        'commerce_product_bundle' => $this->getBundleId(),
      ];
      $options += [
        'query' => [
          'v' => $this->id(),
        ],
        'entity_type' => 'commerce_product_bundle',
        'entity' => $this->getBundle(),
        // Display links by default based on the current language.
        'language' => $this->language(),
      ];
      return new Url($route_name, $route_parameters, $options);
    }
    else {
      return parent::toUrl($rel, $options);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getBundle() {
    return $this->getTranslatedReferencedEntity('bundle_id');
  }

  /**
   * {@inheritdoc}
   */
  public function getBundleId() {
    return $this->get('bundle_id')->target_id;
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
  public function isRequired() {
    return $this->get('required')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setRequired($required) {
    $this->set('required', (bool) $required);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getUnitPrice() {
    if (!$this->get('unit_price')->isEmpty()) {
      return $this->get('unit_price')->first()->toPrice();
    }

    $variation = $this->getCurrentVariation();
    return $variation ? $variation->getPrice() : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function hasUnitPrice() {
    return !$this->get('unit_price')->isEmpty();
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
  public function getQuantity() {
    if (isset($this->activeQuantity)) {
      return $this->activeQuantity;
    }
    return $this->getMinimumQuantity();
  }

  /**
   * {@inheritdoc}
   */
  public function setQuantity($quantity) {

    // @todo We need to check against the min/max constraints
    // @see https://www.drupal.org/node/2847809
    $this->activeQuantity = (float) $quantity;

    return $this;
  }

  /**
   * @inheritdoc
   */
  public function setMinimumQuantity($minimum_quantity) {
    $this->set('min_quantity', (float) $minimum_quantity);

    return $this;
  }

  /**
   * @inheritdoc
   */
  public function setMaximumQuantity($maximum_quantity) {
    $this->set('max_quantity', (float) $maximum_quantity);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasProduct() {
    return !$this->get('product')->isEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function getProduct() {
    if ($this->hasProduct()) {
      return $this->get('product')->referencedEntities()[0];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getProductId() {
    return $this->getProduct()->id();
  }

  /**
   * @inheritdoc
   */
  public function setProduct(ProductInterface $product) {
    // Unset variations, if we get another product.
    if ($this->hasProduct()) {
      $currentProductId = $this->getProduct()->id();
      $newProductId = $product->id();
      if ($currentProductId !== $newProductId) {
        $this->set('variations', NULL);
      }
    }

    $this->set('product', $product);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasVariations() {
    return !$this->get('variations')->isEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function addVariation(ProductVariationInterface $variation) {
    if ($this->hasProduct() && $this->hasVariations() && !$this->hasVariation($variation)) {
      $this->assertSameProduct([$variation]);
      $this->get('variations')->appendItem($variation);
    }

    return $this;
  }

  /**
   * Whether the bundle item has a specific variation referenced.
   *
   * @param \Drupal\commerce_product\Entity\ProductVariationInterface $variation
   *   The variation.
   *
   * @return bool
   *   True if the variations reference contains the variation, false otherwise.
   */
  protected function hasVariation(ProductVariationInterface $variation) {
    return $this->getVariationIndex($variation) !== FALSE;
  }

  /**
   * Get the index of a variation in the variation references.
   *
   * @param \Drupal\commerce_product\Entity\ProductVariationInterface $variation
   *   The variation.
   *
   * @return false|int|string
   *   The key for the variation if it is found in the
   *   references, false otherwise.
   */
  protected function getVariationIndex(ProductVariationInterface $variation) {
    $result = array_search($variation->id(), $this->getVariationIds() ?: []);
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getVariationIds() {

    $variations = $this->getVariations();
    if (empty($variations)) {
      return NULL;
    }

    return array_map(function ($variation) {
      return $variation->id();
    }, $this->getVariations());

  }

  /**
   * {@inheritdoc}
   */
  public function removeVariation(ProductVariationInterface $variation) {
    $index = $this->getVariationIndex($variation);
    if ($index !== FALSE) {
      $this->get('variations')->offsetUnset($index);
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultVariation() {
    if (!$this->hasProduct()) {
      return NULL;
    }
    foreach ($this->getVariations() as $variation) {
      // Return the first active variation.
      if ($variation->isPublished()) {
        return $variation;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getVariations() {

    if (!$this->hasProduct()) {
      return NULL;
    }

    $variations = $this->get('variations')->referencedEntities();
    if (empty($variations)) {
      return $this->getEnabledVariations();
    }

    return $this->ensureTranslations($variations);
  }

  /**
   * Get the enabled product variations.
   *
   * @return null|\Drupal\commerce_product\Entity\ProductVariationInterface[]
   *   Array of enabled product variations or NULL.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function getEnabledVariations() {
    $variationStorage = $this->entityTypeManager()
      ->getStorage('commerce_product_variation');
    return $variationStorage->loadEnabled($this->getProduct());
  }

  /**
   * {@inheritdoc}
   */
  public function setVariations(array $variations) {
    if (empty($variations)) {
      return $this;
    }

    // If there is no product referenced on the bundle item, do it now.
    if ($this->get('product')->isEmpty()) {
      $this->setProduct($variations[0]->getProduct());
    }
    $this->assertSameProduct($variations);

    $this->set('variations', $variations);
    return $this;
  }

  /**
   * Ensure that all passed variations belong to the same product.
   *
   * @param array $variations
   *   \Drupal\commerce_product\Entity\ProductVariationInterface[].
   *
   * @throws \InvalidArgumentException
   *    In case a variation belongs to another product.
   */
  protected function assertSameProduct(array $variations) {
    foreach ($variations as $variation) {
      $shouldBeOfType = $this->getProduct()->id();
      $isType = $variation->getProductId();
      if ($shouldBeOfType !== $isType) {
        throw new \InvalidArgumentException('All variations of a bundle item must be from the same product.');
      }
    }
  }

  /**
   * {@inheritdoc}
   *
   * @todo Figure out how to get the currently selected variation
   * without holding state in this object.
   * @see https://www.drupal.org/node/2831613
   */
  public function getCurrentVariation() {
    return $this->currentVariation ?: $this->getDefaultVariation();
  }

  /**
   * {@inheritdoc}
   */
  public function setCurrentVariation(ProductVariationInterface $variation) {
    $this->assertSameProduct([$variation]);
    if (!$this->hasVariation($variation)) {
      throw new \InvalidArgumentException('Variation is not part of this product bundle.');
    }
    $this->currentVariation = $variation;
    return $this;
  }

  /**
   * @inheritdoc
   */
  public function getMinimumQuantity() {
    return $this->get('min_quantity')->value;
  }

  /**
   * @inheritdoc
   */
  public function getMaximumQuantity() {
    return $this->get('max_quantity')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['store']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTagsToInvalidate() {
    $tags = parent::getCacheTagsToInvalidate();
    // Invalidate the variations view builder and product caches.
    return Cache::mergeTags($tags, [
      'commerce_product_bundle:' . $this->getBundleId(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public static function preCreate(
    EntityStorageInterface $storage_controller,
    array &$values
  ) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'uid' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    // Ensure there's a reference on the parent product bundle.
    $product_bundle = $this->getBundle();
    if ($product_bundle && !$product_bundle->hasBundleItem($this)) {
      $product_bundle->addBundleItem($this);
      $product_bundle->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);

    /** @var \Drupal\commerce_product_bundle\Entity\BundleItemInterface[] $entities */
    foreach ($entities as $bundleItem) {
      // Remove the reference from the parent product bundle.
      /** @var \Drupal\commerce_product_bundle\Entity\BundleInterface $product_bundle */
      $product_bundle = $bundleItem->getBundle();
      if ($product_bundle && $product_bundle->hasBundleItems($bundleItem)) {
        $product_bundle->removeBundleItem($bundleItem);
        $product_bundle->save();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::publishedBaseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['uid']
      ->setLabel(t('Author'))
      ->setDescription(t('The author of the product bundle item.'))
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The title of the product bundle item entity.'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
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

    $fields['required'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Required?'))
      ->setDescription(t('A boolean indicating whether the product bundle item is required or optional.'))
      ->setSettings([
        'on_label' => t('Yes, required'),
        'off_label' => t('No, optional'),
      ])
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => 20,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDefaultValue(TRUE);

    // The product bundle backreference, populated by ProductBundle::postSave().
    $fields['bundle_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Product bundle'))
      ->setDescription(t('The parent product bundle.'))
      ->setSetting('target_type', 'commerce_product_bundle')
      ->setReadOnly(TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // The price is not required because it's not guaranteed to be used
    // for storage. We may use the price of the referenced variations.
    // entity.
    $fields['unit_price'] = BaseFieldDefinition::create('commerce_price')
      ->setLabel(t('Unit price'))
      ->setDescription(t('The unit price, if overridden, of the variation selected from this bundle item.'))
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

    $fields['product'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Product'))
      ->setDescription(t('Reference to a product.'))
      ->setSetting('target_type', 'commerce_product')
      ->setSetting('handler', 'default')
      ->setCardinality(1)
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'entity_reference_label',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'match_limit' => 10,
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['variations'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Variations'))
      ->setDescription(t('The product variations.'))
      ->setSetting('target_type', 'commerce_product_variation')
      ->setSetting('handler', 'default')
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED)
      ->setRequired(FALSE)
      ->setDisplayOptions('view', [
        'type' => 'commerce_add_to_cart',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['min_quantity'] = BaseFieldDefinition::create('decimal')
      ->setLabel(t('Minimum Quantity'))
      ->setDescription(t('The minimum quantity.'))
      ->setSetting('unsigned', TRUE)
      ->setSetting('min', 0)
      ->setRequired(TRUE)
      ->setDefaultValue(1)
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->addPropertyConstraints('value', [
        'Range' => [
          'min' => 0,
        ],
      ]);

    $fields['max_quantity'] = BaseFieldDefinition::create('decimal')
      ->setLabel(t('Maximum Quantity'))
      ->setDescription(t('The maximum quantity.'))
      ->setSetting('unsigned', TRUE)
      ->setSetting('min', 1)
      ->setRequired(TRUE)
      ->setDefaultValue(1)
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => 1,
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

}
