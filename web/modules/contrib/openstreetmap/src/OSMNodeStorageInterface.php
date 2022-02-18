<?php

namespace Drupal\openstreetmap;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\openstreetmap\Entity\OSMNodeInterface;

/**
 * Defines the storage handler class for OSM Node entities.
 *
 * This extends the base storage class, adding required special handling for
 * OSM Node entities.
 *
 * @ingroup openstreetmap
 */
interface OSMNodeStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of OSM Node revision IDs for a specific OSM Node.
   *
   * @param \Drupal\openstreetmap\Entity\OSMNodeInterface $entity
   *   The OSM Node entity.
   *
   * @return int[]
   *   OSM Node revision IDs (in ascending order).
   */
  public function revisionIds(OSMNodeInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as OSM Node author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   OSM Node revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\openstreetmap\Entity\OSMNodeInterface $entity
   *   The OSM Node entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(OSMNodeInterface $entity);

  /**
   * Unsets the language for all OSM Node with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
