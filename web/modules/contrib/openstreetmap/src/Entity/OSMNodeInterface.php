<?php

namespace Drupal\openstreetmap\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;

/**
 * Provides an interface for defining OSM Node entities.
 *
 * @ingroup openstreetmap
 */
interface OSMNodeInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the OSM Node name.
   *
   * @return string
   *   Name of the OSM Node.
   */
  public function getName();

  /**
   * Sets the OSM Node name.
   *
   * @param string $name
   *   The OSM Node name.
   *
   * @return \Drupal\openstreetmap\Entity\OSMNodeInterface
   *   The called OSM Node entity.
   */
  public function setName($name);

  /**
   * Gets the OSM Node creation timestamp.
   *
   * @return int
   *   Creation timestamp of the OSM Node.
   */
  public function getCreatedTime();

  /**
   * Sets the OSM Node creation timestamp.
   *
   * @param int $timestamp
   *   The OSM Node creation timestamp.
   *
   * @return \Drupal\openstreetmap\Entity\OSMNodeInterface
   *   The called OSM Node entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the OSM Node revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the OSM Node revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\openstreetmap\Entity\OSMNodeInterface
   *   The called OSM Node entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the OSM Node revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the OSM Node revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\openstreetmap\Entity\OSMNodeInterface
   *   The called OSM Node entity.
   */
  public function setRevisionUserId($uid);

}
