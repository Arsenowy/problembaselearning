<?php

/**
 * @file
 * Contains osm_node.page.inc.
 *
 * Page callback for OSM Node entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for OSM Node templates.
 *
 * Default template: osm_node.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_osm_node(array &$variables) {
  // Fetch OSMNode Entity Object.
  $osm_node = $variables['elements']['#osm_node'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
