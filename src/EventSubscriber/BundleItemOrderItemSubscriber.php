<?php

namespace Drupal\commerce_product_bundle\EventSubscriber;

use Drupal\commerce_order\Event\OrderItemEvent;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Deletes the bundle items order items if an order item gets deleted.
 */
class BundleItemOrderItemSubscriber implements EventSubscriberInterface {

  /**
   * BundleItemOrderItemSubscriber constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager,.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->bundleItemOrderItemStorage = $entity_type_manager->getStorage('cpb_order_item');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [
      'commerce_order.commerce_order_item.insert' => [
        'saveOrderItemBackReference',
        -50,
      ],
      'commerce_order.commerce_order_item.delete' => [
        'deleteBundleItemOrderItem',
        -50,
      ],
    ];
    return $events;
  }

  /**
   * Deletes the bundle item order items of a deleted order item.
   *
   * @param \Drupal\commerce_order\Event\OrderItemEvent $event
   *   The order item event.
   */
  public function deleteBundleItemOrderItem(OrderItemEvent $event) {
    /** @var \Drupal\commerce_order\Entity\OrderItemInterface $order_item */
    $order_item = $event->getOrderItem();
    if ($order_item->bundle() === 'commerce_product_bundle_default') {
      if ($order_item->hasField('bundle_item_order_items')) {
        $bundle_item_order_items = [];
        foreach ($order_item->get('bundle_item_order_items')
          ->referencedEntities() as $bundle_item_order_item
        ) {
          $bundle_item_order_items[$bundle_item_order_item->id()] = $bundle_item_order_item;
        }
        $this->bundleItemOrderItemStorage->delete($bundle_item_order_items);
      }
    }
  }

  /**
   * Saves the order item back reference to the bundle item order item.
   *
   * @param \Drupal\commerce_order\Event\OrderItemEvent $event
   *   The order item event.
   */
  public function saveOrderItemBackReference(OrderItemEvent $event) {
    $order_item = $event->getOrderItem();
    if ($order_item->bundle() === 'commerce_product_bundle_default' && $order_item->hasField('bundle_item_order_items')) {
      foreach ($order_item->get('bundle_item_order_items')->referencedEntities() as $bundle_item_order_item) {
        if ($bundle_item_order_item->order_item_id->isEmpty()) {
          $bundle_item_order_item->set('order_item_id', $order_item);
          $bundle_item_order_item->save();
        };
      }
    }
  }

}
