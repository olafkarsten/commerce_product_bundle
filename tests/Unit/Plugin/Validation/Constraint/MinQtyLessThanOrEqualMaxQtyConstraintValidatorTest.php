<?php

namespace Drupal\Tests\commerce_product_bundle\Unit\Plugin\Validation\Constraint;

use Drupal\commerce_product_bundle\Entity\BundleItemInterface;
use Drupal\commerce_product_bundle\Plugin\Validation\Constraint\MinQtyLessThanOrEqualMaxQtyConstraint;
use Drupal\commerce_product_bundle\Plugin\Validation\Constraint\MinQtyLessThanOrEqualMaxQtyConstraintValidator;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * Test the MinQtyLessThanOrEqualMaxQty constraint validator.
 *
 * @coversDefaultClass \Drupal\commerce_product_bundle\Plugin\Validation\Constraint\MinQtyLessThanOrEqualMaxQtyConstraintValidator
 * @group commerce_product_bundle
 */
class MinQtyLessThanOrEqualMaxQtyConstraintValidatorTest extends UnitTestCase {

  /**
   * The constraint.
   *
   * @var \Drupal\commerce_product_bundle\Plugin\Validation\Constraint\MinQtyLessThanOrEqualMaxQtyConstraint
   */
  protected $constraint;

  /**
   * The validator.
   *
   * @var \Drupal\commerce_product_bundle\Plugin\Validation\Constraint\MinQtyLessThanOrEqualMaxQtyConstraintValidator
   */
  protected $validator;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->constraint = new MinQtyLessThanOrEqualMaxQtyConstraint();
    $this->validator = new MinQtyLessThanOrEqualMaxQtyConstraintValidator();
  }

  /**
   * @covers ::validate
   *
   * @dataProvider providerTestValidate
   */
  public function testValidate($entity, $expected_violation) {

    // If a violation is expected, then the context's buildViolation method
    // will be called, otherwise it should not be called.
    $context = $this->prophesize(ExecutionContextInterface::class);

    if ($expected_violation) {
      $violation_builder = $this->prophesize(ConstraintViolationBuilderInterface::class);
      $violation_builder->setParameter('%min', 20)->willReturn($violation_builder);
      $violation_builder->setParameter('%max', 10)->willReturn($violation_builder);
      $violation_builder->addViolation()->willReturn($violation_builder);
      $context->buildViolation($expected_violation)->willReturn($violation_builder->reveal())->shouldBeCalled();
    }
    else {
      $context->buildViolation(Argument::any())->shouldNotBeCalled();
    }

    $this->validator->initialize($context->reveal());
    $this->validator->validate($entity, $this->constraint);
  }

  /**
   * Data provider for ::testValidate().
   */
  public function providerTestValidate() {
    $constraint = new MinQtyLessThanOrEqualMaxQtyConstraint();

    $bundleItemMock = $this->prophesize(BundleItemInterface::class);
    $bundleItemMock->getMinimumQuantity()->willReturn(1,0,1,20);
    $bundleItemMock->getMaximumQuantity()->willReturn(1,1,10,10);
    $entity = $bundleItemMock->reveal();

    // Case 1: Default values, Min = Max
    $cases[] = [$entity, FALSE];
    // Case 2: Min < Max, Min = 0
    $cases[] = [$entity, FALSE];
    // Case 3: Min < Max, Min > 0
    $cases[] = [$entity, FALSE];
    // Case 4: Min > Max
    $cases[] = [$entity, $constraint->message];

    return $cases;
  }

}
