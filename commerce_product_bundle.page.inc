<?php

/**
 * @file
 * Contains product_bundle.page.inc.
 *
 * Page callback for Product bundle entities.
 */

use Drupal\Core\Render\Element;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Prepares variables for Product bundle templates.
 *
 * Default template: commerce_product_bundle.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_commerce_product_bundle(array &$variables) {
  // Fetch ProductBundle Entity Object.
  $product_bundle = $variables['elements']['#commerce_product_bundle'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
