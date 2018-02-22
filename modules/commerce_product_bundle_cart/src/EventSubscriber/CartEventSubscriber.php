<?php

namespace Drupal\commerce_product_bundle_cart\EventSubscriber;

use Drupal\commerce_cart\Event\OrderItemComparisonFieldsEvent;
use Drupal\commerce_cart\Event\CartEvents;
use Drupal\commerce_product_bundle\Entity\BundleInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CartEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [
      CartEvents::ORDER_ITEM_COMPARISON_FIELDS => 'filterOrderItemComparisonFields',
    ];
    return $events;
  }

  /**
   * Tries to jump to checkout, skipping cart after adding certain items.
   *
   * @param \Drupal\commerce_cart\Event\OrderItemComparisonFieldsEvent $event
   *   The order item comparison fields event.
   */
  public function filterOrderItemComparisonFields(OrderItemComparisonFieldsEvent $event) {
    if ($event->getOrderItem()->getPurchasedEntity() instanceof BundleInterface) {
      $fields = $event->getComparisonFields();
      $fields[] = 'bundle_order_item_reference';
      $event->setComparisonFields($fields);
    }
  }

}
