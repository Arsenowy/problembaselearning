<?php

namespace Drupal\openstreetmap_queries;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;

/**
 * Provides a view controller for a overpass query entity type.
 */
class OSMQueryViewBuilder extends EntityViewBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getBuildDefaults(EntityInterface $entity, $view_mode) {
    $build = parent::getBuildDefaults($entity, $view_mode);
    // The overpass query has no entity template itself.
    unset($build['#theme']);
    return $build;
  }

}
