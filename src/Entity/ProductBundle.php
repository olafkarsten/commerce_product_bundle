<?php

namespace Drupal\commerce_product_bundle\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the product bundle entity.
 *
 * @ingroup commerce_product_bundle
 *
 * @ContentEntityType(
 *   id = "commerce_product_bundle",
 *   label = @Translation("Product bundle"),
 *   bundle_label = @Translation("Product bundle type"),
 *   handlers = {
 *     "storage" = "Drupal\commerce_product_bundle\ProductBundleBundleStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\commerce_product_bundle\ProductBundleListBuilder",
 *     "views_data" = "Drupal\commerce_product_bundle\Entity\ProductBundleViewsData",
 *     "translation" = "Drupal\commerce_product_bundle\ProductBundleTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\commerce_product_bundle\Form\ProductBundleForm",
 *       "add" = "Drupal\commerce_product_bundle\Form\ProductBundleForm",
 *       "edit" = "Drupal\commerce_product_bundle\Form\ProductBundleForm",
 *       "delete" = "Drupal\commerce_product_bundle\Form\ProductBundleDeleteForm",
 *     },
 *     "access" = "Drupal\commerce_product_bundle\ProductBundleAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\commerce_product_bundle\ProductBundleHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "commerce_product_bundle",
 *   data_table = "commerce_product_bundle_field_data",
 *   revision_table = "commerce_product_bundle_revision",
 *   revision_data_table = "commerce_product_bundle_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer product bundle entities",
 *   permission_granularity = "bundle",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "revision" = "vid",
 *     "label" = "title",
 *     "uuid" = "uuid",
 *     "uid" = "uid",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/commerce/config/product-bundles/{commerce_product_bundle}",
 *     "add-page" = "/admin/commerce/config/product-bundles/add",
 *     "add-form" = "/admin/commerce/config/product-bundles/add/{commerce_product_bundle_type}",
 *     "edit-form" = "/admin/commerce/config/product-bundles/{commerce_product_bundle}/edit",
 *     "delete-form" = "/admin/commerce/config/product-bundles/{commerce_product_bundle}/delete",
 *     "version-history" = "/admin/commerce/config/product-bundles/{commerce_product_bundle}/revisions",
 *     "revision" = "/admin/commerce/config/product-bundles/{commerce_product_bundle}/revisions/{revision}/view",
 *     "revision_revert" = "/admin/commerce/config/product-bundles/{commerce_product_bundle}/revisions/{revision}/revert",
 *     "translation_revert" = "/admin/commerce/config/product-bundles/{commerce_product_bundle}/revisions/{revision}/revert/{langcode}",
 *     "revision_delete" = "/admin/commerce/config/product-bundles/{commerce_product_bundle}/revisions/{revision}/delete",
 *     "collection" = "/admin/commerce/config/product-bundles",
 *   },
 *   bundle_entity_type = "commerce_product_bundle_type",
 *   field_ui_base_route = "entity.commerce_product_bundle_type.edit_form"
 * )
 */
class ProductBundle extends RevisionableContentEntityBase implements BundleInterface {

  use EntityChangedTrait;

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
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly, make the product_bundle owner the
    // revision author.
    if (!$this->getRevisionAuthor()) {
      $this->setRevisionAuthorId($this->getOwnerId());
    }
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
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Author'))
      ->setDescription(t('The user ID of author of the product bundle entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'author',
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

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The title of the product bundle entity.'))
      ->setRevisionable(TRUE)
      ->setSettings(array(
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDefaultValue('')
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // The price is not required because it's not guaranteed to be used
    // for storage. We may use the price of the referenced purchasable
    // entity.
    $fields['product_bundle_base_price'] = BaseFieldDefinition::create('commerce_price')
      ->setLabel(t('The base price of a the product bundle'))
      ->setDescription(t('The product bundle base price. If set, the prices of  the product bundle items will be ignored. Set only, if you want a global price per product bundle, independent from its items.'))
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
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE);

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

  public function getStores() {
    // TODO: Collect the stores of the bundleItems.
  }

  public function getOrderItemTypeId() {
    // TODO: Implement getOrderItemTypeId() method.
  }

  public function getOrderItemTitle() {
    // TODO: Implement getOrderItemTitle() method.
  }

  public function getPrice() {
    // TODO: Implement getPrice() method.
  }

  public function getBundleItems() {
    // TODO: Implement getBundleItems() method.
  }

  public function setBundleItems(array $bundleItems) {
    // TODO: Implement setBundleItems() method.
  }

  public function addBundleItem(BundleItemInterface $bundleItem) {
    // TODO: Implement addBundleItem() method.
  }

  public function removeBundleItem(BundleItemInterface $bundleItem) {
    // TODO: Implement removeBundleItem() method.
  }
}
