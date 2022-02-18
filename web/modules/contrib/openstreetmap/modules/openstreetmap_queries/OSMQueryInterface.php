<?php

namespace Drupal\openstreetmap_queries;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Provides an interface defining a overpass query entity type.
 */
interface OSMQueryInterface extends ContentEntityInterface, EntityChangedInterface {

  /**
   * Gets the overpass query title.
   *
   * @return string
   *   Title of the overpass query.
   */
  public function getTitle();

  /**
   * Sets the overpass query title.
   *
   * @param string $title
   *   The overpass query title.
   *
   * @return \Drupal\openstreetmap_queries\OSMQueryInterface
   *   The called overpass query entity.
   */
  public function setTitle($title);

  /**
   * Gets the overpass query creation timestamp.
   *
   * @return int
   *   Creation timestamp of the overpass query.
   */
  public function getCreatedTime();

  /**
   * Sets the overpass query creation timestamp.
   *
   * @param int $timestamp
   *   The overpass query creation timestamp.
   *
   * @return \Drupal\openstreetmap_queries\OSMQueryInterface
   *   The called overpass query entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the overpass query status.
   *
   * @return bool
   *   TRUE if the overpass query is enabled, FALSE otherwise.
   */
  public function isEnabled();

  /**
   * Sets the overpass query status.
   *
   * @param bool $status
   *   TRUE to enable this overpass query, FALSE to disable.
   *
   * @return \Drupal\openstreetmap_queries\OSMQueryInterface
   *   The called overpass query entity.
   */
  public function setStatus($status);

}
