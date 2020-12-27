<?php

namespace Drupal\commerce_product_bundle\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the product bundle type form.
 *
 * @package Drupal\commerce_product_bundle\Form
 */
class ProductBundleTypeForm extends EntityForm {

  /**
   * The variation type storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $bundleItemTypeStorage;

  /**
   * ProductBundleTypeForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->bundleItemTypeStorage = $entity_type_manager->getStorage('commerce_product_bundle_i_type');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
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
    $bundle_item_types = array_map(function ($bundle_item_type) {
      return $bundle_item_type->label();
    }, $bundle_item_types);

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
      '#required' => TRUE,
      '#disabled' => !$product_bundle_type->isNew(),
    ];

    if ($this->moduleHandler->moduleExists('commerce_order')) {
      // Prepare a list of order item types used to purchase product bundles.
      $order_item_type_storage = $this->entityTypeManager->getStorage('commerce_order_item_type');
      $order_item_types = $order_item_type_storage->loadMultiple();
      $order_item_types = array_filter($order_item_types, function ($order_item_type) {
        return $order_item_type->getPurchasableEntityTypeId() == 'commerce_product_bundle';
      });
      $order_item_types = array_map(function ($order_item_type) {
        return $order_item_type->label();
      }, $order_item_types);

      $form['orderItemType'] = [
        '#type' => 'select',
        '#title' => $this->t('Order item type'),
        '#default_value' => $product_bundle_type->getOrderItemTypeId(),
        '#options' => $order_item_types,
        '#empty_value' => '',
        '#required' => TRUE,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\commerce_product_bundle\Entity\BundleTypeInterface $product_bundle_type */
    $product_bundle_type = $this->entity;

    $status = $product_bundle_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label product bundle type.', [
          '%label' => $product_bundle_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label product bundle type.', [
          '%label' => $product_bundle_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($product_bundle_type->toUrl('collection'));
    if ($status == SAVED_NEW) {
      commerce_product_bundle_add_stores_field($product_bundle_type);
      commerce_product_bundle_add_body_field($product_bundle_type);
      commerce_product_bundle_add_items_field($product_bundle_type);
    }
  }

}
