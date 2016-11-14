<?php

/**
 * @file
 * Contains static_bundle_item.page.inc.
 *
 * Page callback for Static bundle item entities.
 */

use Drupal\Core\Render\Element;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Prepares variables for Static bundle item templates.
 *
 * Default template: commerce_static_bundle_item.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_commerce_static_bundle_item(array &$variables) {
  // Fetch StaticBundleItem Entity Object.
  $static_bundle_item = $variables['elements']['#commerce_static_bundle_item'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
