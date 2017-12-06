<?php

namespace Drupal\commerce_product_bundle\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the MinQtyLessThanOrEqualMaxQty constraint.
 */
class MinQtyLessThanOrEqualMaxQtyConstraintValidator extends ConstraintValidator {

  /**
   * @inheritdoc
   */
  public function validate($entity, Constraint $constraint) {
    if (!isset($entity)) {
      return;
    }
    $max = $entity->getMaximumQuantity();
    $min = $entity->getMinimumQuantity();

    if ($min > $max) {
      $this->context->buildViolation($constraint->message)
        ->setParameter('%min', $min)
        ->setParameter('%max', $max)
        ->addViolation();
    }
  }

}
