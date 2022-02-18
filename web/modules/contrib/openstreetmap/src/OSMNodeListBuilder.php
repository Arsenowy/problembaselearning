<?php

namespace Drupal\openstreetmap;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of OSM Node entities.
 *
 * @ingroup openstreetmap
 */
class OSMNodeListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('OSM Node ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\openstreetmap\Entity\OSMNode $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.osm_node.canonical',
      ['osm_node' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
