<?php

namespace Drupal\commerce_product_bundle\ContextProvider;

use Drupal\commerce_product_bundle\Entity\BundleTypeInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\EntityContextDefinition;
use Drupal\Core\Plugin\Context\ContextProviderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Sets the current product bundle as context on commerce_product routes.
 *
 * @todo Remove once core gets a generic EntityRouteContext.
 */
class ProductBundleRouteContext implements ContextProviderInterface {

  use StringTranslationTrait;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new ProductBundleRouteContext object.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager) {
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getRuntimeContexts(array $unqualified_context_ids) {
    $context_definition = new EntityContextDefinition('entity:commerce_product_bundle', NULL, FALSE);
    $value = NULL;
    if ($product_bundle = $this->routeMatch->getParameter('commerce_product_bundle')) {
      $value = $product_bundle;
    }
    /** @var \Drupal\commerce_product_bundle\Entity\BundleTypeInterface $product_bundle_type */
    elseif ($product_bundle_type = $this->routeMatch->getParameter('commerce_product_bundle_type')) {
      $product_bundle_storage = $this->entityTypeManager->getStorage('commerce_product_bundle');
      $product_bundle_type_id = $product_bundle_type instanceof BundleTypeInterface ? $product_bundle_type->id() : $product_bundle_type;
      $value = $product_bundle_storage->createWithSampleValues($product_bundle_type_id);
    }

    $cacheability = new CacheableMetadata();
    $cacheability->setCacheContexts(['route']);
    $context = new Context($context_definition, $value);
    $context->addCacheableDependency($cacheability);

    return ['commerce_product_bundle' => $context];
  }

  /**
   * {@inheritdoc}
   */
  public function getAvailableContexts() {
    $context = new Context(new EntityContextDefinition(
      'entity:commerce_product_bundle', $this->t('Product bundle from URL')
    ));
    return ['commerce_product_bundle' => $context];
  }

}
