<?php

namespace Drupal\field_encrypt_searchable\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;

/**
 * Provides an interface for defining Blind Index entities.
 *
 * @ingroup field_encrypt_searchable
 */
interface BlindIndexEntityInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Blind Index name.
   *
   * @return string
   *   Name of the Blind Index.
   */
  public function getName();

  /**
   * Sets the Blind Index name.
   *
   * @param string $name
   *   The Blind Index name.
   *
   * @return \Drupal\field_encrypt_searchable\Entity\BlindIndexEntityInterface
   *   The called Blind Index entity.
   */
  public function setName($name);

  /**
   * Gets the Blind Index creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Blind Index.
   */
  public function getCreatedTime();

  /**
   * Sets the Blind Index creation timestamp.
   *
   * @param int $timestamp
   *   The Blind Index creation timestamp.
   *
   * @return \Drupal\field_encrypt_searchable\Entity\BlindIndexEntityInterface
   *   The called Blind Index entity.
   */
  public function setCreatedTime($timestamp);

}
