<?php

/**
 * @file
 * Contains static_bundle.page.inc.
 *
 * Page callback for Static bundle entities.
 */

use Drupal\Core\Render\Element;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Prepares variables for Static bundle templates.
 *
 * Default template: static_bundle.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_static_bundle(array &$variables) {
  // Fetch StaticBundle Entity Object.
  $static_bundle = $variables['elements']['#static_bundle'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
