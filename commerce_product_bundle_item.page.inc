<?php

/**
 * @file
 * Contains product_bundle_item.page.inc.
 *
 * Page callback for Product bundle item entities.
 */

use Drupal\Core\Render\Element;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Prepares variables for Product bundle item templates.
 *
 * Default template: commerce_product_bundle_i.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_commerce_product_bundle_i(array &$variables) {
  // Fetch ProductBundleItem Entity Object.
  $product_bundle_item = $variables['elements']['#commerce_product_bundle_i'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
