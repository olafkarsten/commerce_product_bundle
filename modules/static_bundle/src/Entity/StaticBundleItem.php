<?php

namespace Drupal\commerce_static_bundle\Entity;

use Drupal\commerce_product_bundle\Entity\BundleItemInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Static bundle item entity.
 *
 * @ingroup commerce_static_bundle
 *
 * @ContentEntityType(
 *   id = "static_bundle_item",
 *   label = @Translation("Static bundle item"),
 *   bundle_label = @Translation("Static bundle item type"),
 *   handlers = {
 *     "storage" = "Drupal\commerce_static_bundle\StaticBundleItemStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\commerce_static_bundle\StaticBundleItemListBuilder",
 *     "views_data" = "Drupal\commerce_static_bundle\Entity\StaticBundleItemViewsData",
 *     "translation" = "Drupal\commerce_static_bundle\StaticBundleItemTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\commerce_static_bundle\Form\StaticBundleItemForm",
 *       "add" = "Drupal\commerce_static_bundle\Form\StaticBundleItemForm",
 *       "edit" = "Drupal\commerce_static_bundle\Form\StaticBundleItemForm",
 *       "delete" = "Drupal\commerce_static_bundle\Form\StaticBundleItemDeleteForm",
 *     },
 *     "access" = "Drupal\commerce_static_bundle\StaticBundleItemAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\commerce_static_bundle\StaticBundleItemHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "static_bundle_item",
 *   data_table = "static_bundle_item_field_data",
 *   revision_table = "static_bundle_item_revision",
 *   revision_data_table = "static_bundle_item_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer static bundle item entities",
 *   entity_keys = {
 *     "id" = "bundle_item_id",
 *     "bundle" = "type",
 *     "revision" = "vid",
 *     "label" = "title",
 *     "uuid" = "uuid",
 *     "uid" = "uid",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/commerce/product-bundle/static-bundle/bundle-item/static_bundle_item/{static_bundle_item}",
 *     "add-form" = "/admin/commerce/product-bundle/static-bundle/bundle-item/static_bundle_item/add",
 *     "edit-form" = "/admin/commerce/product-bundle/static-bundle/bundle-item/static_bundle_item/{static_bundle_item}/edit",
 *     "delete-form" = "/admin/commerce/product-bundle/static-bundle/bundle-item/static_bundle_item/{static_bundle_item}/delete",
 *     "version-history" = "/admin/commerce/product-bundle/static-bundle/bundle-item/static_bundle_item/{static_bundle_item}/revisions",
 *     "revision" = "/admin/commerce/product-bundle/static-bundle/bundle-item/static_bundle_item/{static_bundle_item}/revisions/{static_bundle_item_revision}/view",
 *     "revision_revert" = "/admin/commerce/product-bundle/static-bundle/bundle-item/static_bundle_item/{static_bundle_item}/revisions/{static_bundle_item_revision}/revert",
 *     "translation_revert" = "/admin/commerce/product-bundle/static-bundle/bundle-item/static_bundle_item/{static_bundle_item}/revisions/{static_bundle_item_revision}/revert/{langcode}",
 *     "revision_delete" = "/admin/commerce/product-bundle/static-bundle/bundle-item/static_bundle_item/{static_bundle_item}/revisions/{static_bundle_item_revision}/delete",
 *     "collection" = "/admin/commerce/product-bundle/static-bundle/bundle-item/static_bundle_item",
 *   },
 *   bundle_entity_type = "static_bundle_item_type",
 *   field_ui_base_route = "entity.static_bundle_item_type.edit_form"
 * )
 */
class StaticBundleItem extends RevisionableContentEntityBase implements BundleItemInterface {

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

    // If no revision author has been set explicitly, make the static_bundle_item owner the
    // revision author.
    if (!$this->getRevisionAuthor()) {
      $this->setRevisionAuthorId($this->getOwnerId());
    }
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
  public function getType() {
    return $this->bundle();
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
      ->setDescription(t('The user ID of author of the Static bundle item entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\commerce_static_bundle\Entity\StaticBundleItem::getCurrentUserId')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'title' => 'hidden',
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
      ->setDescription(t('The title of the Static bundle item entity.'))
      ->setRequired(TRUE)
      >setTranslatable(TRUE)
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

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Static bundle item is published.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE);

    // The price is not required because it's not guaranteed to be used
    // for storage. We may use the price of the referenced purchasable
    // entity.
    $fields['bundle_item_price'] = BaseFieldDefinition::create('commerce_price')
      ->setLabel(t('The price of a Single Bundle Item'))
      ->setDescription(t('The bundle item price'))
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

    $fields['quantity'] = BaseFieldDefinition::create('decimal')
      ->setLabel(t('Quantity'))
      ->setDescription(t('The number of purchasable entities this item contains.'))
      ->setSetting('unsigned', TRUE)
      ->setRequired(TRUE)
      ->setDefaultValue(1)
      ->setDisplayOptions('form', [
        'type' => 'number',
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

  public function getStores() {
    // TODO: Implement getStores() method.
  }

  public function getOrderItemTypeId() {
    // TODO: Implement getOrderItemTypeId() method.
  }

  public function getOrderItemTitle() {
    // TODO: Implement getOrderItemTitle() method.
  }

  /**
   * {@inheritdoc}
   */
  public function getPrice() {
    if (!$this->get('price')->isEmpty()) {
      return $this->get('price')->first()->toPrice();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setPrice(Price $price) {
    $this->set('price', $price);
    return $this;
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

}
