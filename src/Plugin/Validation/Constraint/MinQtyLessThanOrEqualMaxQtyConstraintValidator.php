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
    $maxQuantity = $entity->getMaximumQuantity();
    $minQuantity = $entity->getMinimumQuantity();

    if ($minQuantity > $maxQuantity) {
      $this->context->buildViolation($constraint->message)
        ->setParameter('%min', $minQuantity)
        ->setParameter('%max', $maxQuantity)
        ->addViolation();
    }
  }

}
