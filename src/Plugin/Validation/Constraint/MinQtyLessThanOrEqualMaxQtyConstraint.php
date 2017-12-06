<?php

namespace Drupal\commerce_product_bundle\Plugin\Validation\Constraint;

use Drupal\Core\Entity\Plugin\Validation\Constraint\CompositeConstraintBase;

/**
 * Helps validating that the minimum qty value is less than or equal to the maximum qty.
 *
 * @Constraint(
 *   id = "MinQtyLessThanOrEqualMaxQty",
 *   label = @Translation("Checks that minimum quantity is less or equal than maximum quantity.", context = "Validation"),
 *   type = "entity:product_bundle_item"
 * )
 */
class MinQtyLessThanOrEqualMaxQtyConstraint extends CompositeConstraintBase {

  /**
   * Message that will be shown if validation fails.
   *
   * @var string
   */
  public $message = 'Minimum Quantity (%min) must be less than or equal to Maximum Quantity (%max).';

  /**
   * @inheritdoc
   */
  public function coversFields() {
    return ['min_quantity', 'max_quantity'];
  }

}
