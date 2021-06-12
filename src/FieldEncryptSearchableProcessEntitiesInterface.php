<?php

namespace Drupal\field_encrypt_searchable;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;


/**
 * Interface for service class to process entities and fields for encryption.
 */
interface FieldEncryptSearchableProcessEntitiesInterface {

  /**
   * Check if entity has blind index fields.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to check.
   *
   * @return array
   *   Return array of name of blind index fields.
   */
  public function entityHasBlindIndexFields(ContentEntityInterface $entity): array;

  /**
   * @param EntityInterface $entity
   */
  public function processBlindIndex(EntityInterface $entity);

  /**
   * @param EntityInterface $entity
   */
  public function deleteBlindIndex(EntityInterface $entity);
}
