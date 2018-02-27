<?php

namespace Drupal\commerce_product_bundle\Form;

use Drupal\commerce_product_bundle\Entity\BundleItemOrderItemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\inline_entity_form\Form\EntityInlineForm;

/**
 * Defines the inline form for order items.
 */
class BundleItemOrderItemInlineForm extends EntityInlineForm {

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeLabels() {
    $labels = [
      'singular' => t('product bundle item order item'),
      'plural' => t('product bundle item order items'),
    ];
    return $labels;
  }

  /**
   * {@inheritdoc}
   */
  public function getTableFields($bundles) {
    $fields = parent::getTableFields($bundles);
    $fields['purchased_entity'] = [
      'type' => 'field',
      'label' => t('Purchased Entity'),
      'weight' => 2,
    ];
    $fields['unit_price'] = [
      'type' => 'field',
      'label' => t('Unit price'),
      'weight' => 2,
    ];
    $fields['quantity'] = [
      'type' => 'field',
      'label' => t('Quantity'),
      'weight' => 3,
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function entityForm(array $entity_form, FormStateInterface $form_state) {
    $entity_form = parent::entityForm($entity_form, $form_state);
    $entity_form['#entity_builders'][] = [get_class($this), 'populateTitle'];

    return $entity_form;
  }

  /**
   * Entity builder: populates the bundle item order item title from the bundle item.
   *
   * @param string $entity_type
   *   The entity type identifier.
   * @param \Drupal\commerce_product_bundle\Entity\BundleItemOrderItemInterface $order_item
   *   The order item.
   * @param array $form
   *   The complete form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public static function populateTitle($entity_type, BundleItemOrderItemInterface $order_item, array $form, FormStateInterface $form_state) {
    /** @var \Drupal\commerce_product_bundle\Entity\BundleItemInterface $bundle_item */
    $bundle_item = $order_item->getBundleItem();
    $bundle_item->setCurrentVariation($order_item->getPurchasedEntity());
    if ($order_item->isNew()) {
      $order_item->setTitle($bundle_item->getTitle());
    }
  }

}
