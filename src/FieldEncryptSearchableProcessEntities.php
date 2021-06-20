<?php

namespace Drupal\field_encrypt_searchable;

use Drupal;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldConfigStorageBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\field_encrypt_searchable\Entity\BlindIndexEntity;
use Drupal\field_encrypt_searchable\Transformation\SearchLikeTransformation;
use ParagonIE\CipherSweet\Backend\FIPSCrypto;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\CipherSweet;
use ParagonIE\CipherSweet\EncryptedField;
use ParagonIE\CipherSweet\KeyProvider\StringProvider;

/**
 * Service class to process entities and fields for blind index.
 */
class FieldEncryptSearchableProcessEntities implements FieldEncryptSearchableProcessEntitiesInterface
{

  /**
   * {@inheritdoc}
   */
  public function entityHasBlindIndexFields(ContentEntityInterface $entity): array
  {
    // Make sure we can get fields.
    if (!is_callable([$entity, 'getFields'])) {
      return [];
    }

    $blindIndexFields = [];
    foreach ($entity->getFields() as $field) {
      if ($this->checkField($field)) {
        $blindIndexFields[] = $field->getName();
      }
    }

    return $blindIndexFields;
  }

  /**
   * Check if a given field has encryption enabled.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $field
   *   The field to check.
   *
   * @return bool
   *   Boolean indicating whether to encrypt the field.
   */
  protected function checkField(FieldItemListInterface $field): bool
  {
    if (!is_callable([$field, 'getFieldDefinition'])) {
      return FALSE;
    }

    /* @var $definition BaseFieldDefinition */
    $definition = $field->getFieldDefinition();

    if (!is_callable([$definition, 'get'])) {
      return FALSE;
    }

    /* @var $storage FieldConfigStorageBase */
    $storage = $definition->get('fieldStorage');
    if (is_null($storage)) {
      return FALSE;
    }

    // Check if the field is encrypted.
    $hasBlindIndex = $storage->getThirdPartySetting('field_encrypt', 'encrypt', FALSE) && $storage->getThirdPartySetting('field_encrypt', 'blind_index', FALSE);
    if ($hasBlindIndex) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function processBlindIndex(EntityInterface $entity) {
    Drupal::service('field_encrypt.process_entities')->decryptEntity($entity);
    $blindIndexFields = $this->entityHasBlindIndexFields($entity);
    if (!empty($blindIndexFields)) {
      foreach ($blindIndexFields as $blindIndexField) {
        if ($entity->hasField($blindIndexField)) {
          /* @var $definition BaseFieldDefinition */
          $definition = $entity->{$blindIndexField}->getFieldDefinition();
          /* @var $storage FieldConfigStorageBase */
          $storage = $definition->get('fieldStorage');
          $fieldType = $definition->getType();
          $encryption_profile_id = $storage->getThirdPartySetting('field_encrypt', 'encryption_profile', []);
          $encryption_profile = Drupal::service('encrypt.encryption_profile.manager')
            ->getEncryptionProfile($encryption_profile_id);

          $provider = new StringProvider($encryption_profile->getEncryptionKey()->getKeyValue());
          $engine = new CipherSweet($provider, new FIPSCrypto());

          $values = $entity->{$blindIndexField}->getValue();
          $existIndexNames = $existDelta = [];
          $query = [
            'langcode' => $entity->language()->getId() ?? Drupal::languageManager()->getDefaultLanguage()->getId(),
            'status' => true,
            'entity_id' => $entity->id(),
            'entity_type_id' => $entity->getEntityTypeId(),
            'entity_bundle' => $entity->bundle(),
            'entity_field' => $blindIndexField,
          ];
          foreach ($values as $key => $value) {
            $value = reset($value);
            $indexes = [];
            for ($length = 1; $length <= mb_strlen($value); $length++) {
              for ($position = 0; $position <= mb_strlen($value) - $length; $position++) {
                $prepareBlindIndex = new EncryptedField($engine);
                $prepareBlindIndex->addBlindIndex(
                  new BlindIndex(
                    "encrypt_searchable",
                    [new SearchLikeTransformation($position, $length)],
                    128
                  )
                );
                $indexes["{$fieldType}-[{$position}_{$length}]"] = $prepareBlindIndex->getBlindIndex($value, 'encrypt_searchable');
              }
            }

            $deleteQuery = Drupal::entityQuery('blind_index_entity');
            foreach ($query as $field => $field_value) {
              $deleteQuery->condition($field, $field_value);
            }
            $deletedIndexIds = $deleteQuery->execute();
            foreach ($deletedIndexIds as $deletedIndexId) {
              BlindIndexEntity::load($deletedIndexId)->delete();
            }

            foreach ($indexes as $indexName => $index) {
              BlindIndexEntity::create($query + [
                  'name' => $indexName,
                  'index_value' => $index,
                  'entity_field_delta' => $key,
                ])->save();
            }
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function deleteBlindIndex(EntityInterface $entity) {
    Drupal::service('field_encrypt.process_entities')->decryptEntity($entity);
    $blindIndexFields = $this->entityHasBlindIndexFields($entity);
    if (!empty($blindIndexFields)) {
      foreach ($blindIndexFields as $blindIndexField) {
        if ($entity->hasField($blindIndexField)) {
          $query = [
            'status' => true,
            'entity_id' => $entity->id(),
            'entity_type_id' => $entity->getEntityTypeId(),
            'entity_bundle' => $entity->bundle(),
            'entity_field' => $blindIndexField,
          ];
          $deleteQuery = Drupal::entityQuery('blind_index_entity');
          foreach ($query as $key => $value) {
            $deleteQuery->condition($key, $value);
          }
          $deletedIndexIds = $deleteQuery->execute();
          foreach ($deletedIndexIds as $deletedIndexId) {
            BlindIndexEntity::load($deletedIndexId)->delete();
          }
        }
      }
    }
  }

}
