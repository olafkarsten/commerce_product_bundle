<?php

namespace Drupal\commerce_product_bundle\Form;

use Drupal\commerce\EntityHelper;
use Drupal\commerce\EntityTraitManagerInterface;
use Drupal\commerce\Form\CommerceBundleEntityFormBase;
use Drupal\commerce_order\Entity\OrderItemTypeInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\entity\Form\EntityDuplicateFormTrait;
use Drupal\language\Entity\ContentLanguageSettings;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the product bundle type form.
 *
 * @package Drupal\commerce_product_bundle\Form
 */
class ProductBundleTypeForm extends CommerceBundleEntityFormBase {

  use EntityDuplicateFormTrait;

  /**
   * The variation type storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $bundleItemTypeStorage;

  /**
   * ProductBundleTypeForm constructor.
   *
   * @param \Drupal\commerce\EntityTraitManagerInterface $trait_manager
   *   The entity trait manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function __construct(
    EntityTraitManagerInterface $trait_manager,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct($trait_manager);
    $this->bundleItemTypeStorage = $entity_type_manager->getStorage('commerce_product_bundle_i_type');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.commerce_entity_trait'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\commerce_product_bundle\Entity\BundleTypeInterface $product_bundle_type */
    $product_bundle_type = $this->entity;

    $bundle_item_types = $this->bundleItemTypeStorage->loadMultiple();
    $bundle_item_types = EntityHelper::extractLabels($bundle_item_types);

    // Create an empty product to get the default status value.
    // @todo Clean up once https://www.drupal.org/node/2318187 is fixed.
    if (in_array($this->operation, ['add', 'duplicate'])) {
      $product_bundle = $this->entityTypeManager->getStorage('commerce_product_bundle')
        ->create(['type' => $product_bundle_type->uuid()]);
      $product_bundles_exist = FALSE;
    }
    else {
      $storage = $this->entityTypeManager->getStorage('commerce_product_bundle');
      $product_bundle = $storage->create(['type' => $product_bundle_type->id()]);
      $product_bundles_exist = $storage->getQuery()
        ->condition('type', $product_bundle_type->id())
        ->execute();
    }
    $form_state->set('original_entity', $this->entity->createDuplicate());

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $product_bundle_type->label(),
      '#description' => $this->t("Label for the product bundle type."),
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $product_bundle_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\commerce_product_bundle\Entity\ProductBundleType::load',
      ],
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#disabled' => !$product_bundle_type->isNew(),
    ];
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#description' => $this->t('This text will be displayed on the <em>Add product bundle</em> page.'),
      '#default_value' => $product_bundle_type->getDescription(),
    ];
    $form['bundleItemType'] = [
      '#type' => 'select',
      '#title' => $this->t('Product bundle item type'),
      '#default_value' => $product_bundle_type->getBundleItemTypeId(),
      '#options' => $bundle_item_types,
      '#disabled' => $product_bundles_exist,
    ];
    if ($product_bundle_type->isNew()) {
      $form['bundleItemType']['#empty_option'] = $this->t('- Create new -');
      $form['bundleItemType']['#description'] = $this->t('If an existing product bundle item type is not selected, a new one will be created.');
    }

    if ($this->moduleHandler->moduleExists('commerce_order')) {
      // Prepare a list of order item types used to purchase product bundles.
      $order_item_types = $this->getOrderItemTypes();
      $order_item_types = EntityHelper::extractLabels($order_item_types);
      reset($order_item_types);

      $form['orderItemType'] = [
        '#type' => 'select',
        '#title' => $this->t('Order item type'),
        '#default_value' => $product_bundle_type->getOrderItemTypeId(),
        '#options' => $order_item_types,
        '#empty_value' => '',
      ];
      if (count($order_item_types) == 1) {
        $form['orderItemType']['#disabled'] = TRUE;
        $form['orderItemType']['#value'] = key($order_item_types);
      }
    }

    if ($this->moduleHandler->moduleExists('language')) {
      $form['language'] = [
        '#type' => 'details',
        '#title' => $this->t('Language settings'),
        '#group' => 'additional_settings',
      ];
      $form['language']['language_configuration'] = [
        '#type' => 'language_configuration',
        '#entity_information' => [
          'entity_type' => 'commerce_product_bundle',
          'bundle' => $product_bundle_type->id(),
        ],
        '#default_value' => ContentLanguageSettings::loadByEntityTypeBundle('commerce_product_bundle', $product_bundle_type->id()),
      ];
      $form['#submit'][] = 'language_configuration_element_submit';
    }

    $form = $this->buildTraitForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->validateTraitForm($form, $form_state);
    if (empty($form_state->getValue('bundleItemType'))) {
      $id = $form_state->getValue('id');
      if (!empty($this->entityTypeManager->getStorage('commerce_product_bundle_i_type')
        ->load($id))) {
        $form_state->setError($form['bundleItemType'], $this->t('A product bundle item type with the machine name @id already exists. Select an existing product bundle item type or change the machine name for this product bundle type.', [
          '@id' => $id,
        ]));
      }
    }

    if ($this->moduleHandler->moduleExists('commerce_order')) {
      $order_item_type_ids = array_keys($this->getOrderItemTypes());
      if (empty($order_item_type_ids)) {
        $form_state->setError($form['orderItemType'], $this->t('A new product bundle type cannot be created, because no order item types were found. Select an existing product bundle type or retry after creating a new order item type.'));
      }
    }

  }

  /**
   * Provides available order item types.
   *
   * @return array Drupal\commerce_order\Entity\OrderItemTypeInterface[]
   *   The order item types available.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getOrderItemTypes() {
    $order_item_type_storage = $this->entityTypeManager->getStorage('commerce_order_item_type');
    $order_item_types = $order_item_type_storage->loadMultiple();
    return array_filter($order_item_types, function (
      OrderItemTypeInterface $type
    ) {
      return $type->getPurchasableEntityTypeId() == 'commerce_product_bundle';
    });

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    /** @var \Drupal\commerce_product_bundle\Entity\BundleTypeInterface $product_bundle_type */
    $product_bundle_type = $this->entity;
    // Create a new product bundle item type.
    if (empty($form_state->getValue('bundleItemType'))) {
      /** @var \Drupal\commerce_product_bundle\Entity\BundleItemTypeInterface $bundle_item_type */
      $bundle_item_type = $this->entityTypeManager->getStorage('commerce_product_bundle_i_type')
        ->create([
          'id' => $form_state->getValue('id'),
          'label' => $form_state->getValue('label'),
        ]);
      $bundle_item_type->save();
      $product_bundle_type->setBundleItemTypeId($form_state->getValue('id'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\commerce_product_bundle\Entity\BundleTypeInterface $product_bundle_type */
    $product_bundle_type = $this->entity;

    $product_bundle_type->save();
    $this->postSave($product_bundle_type, $this->operation);

    if ($this->operation == 'add') {
      commerce_product_bundle_add_body_field($product_bundle_type);
    }

    $this->submitTraitForm($form, $form_state);

    $this->messenger()
      ->addMessage($this->t('The product bundle type %label has been successfully saved.', ['%label' => $product_bundle_type->label()]));
    $form_state->setRedirect('entity.commerce_product_bundle_type.collection');
  }

}
