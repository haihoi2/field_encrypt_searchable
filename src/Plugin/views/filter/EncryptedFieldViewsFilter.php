<?php

namespace Drupal\field_encrypt_searchable\Plugin\views\filter;

/**
 * Encrypted field views filter.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("encrypted_field_filter")
 */

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\StringFilter;

class EncryptedFieldViewsFilter extends StringFilter {

  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['fields'] = ['default' => ''];

    return $options;
  }

  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $this->view->initStyle();

    // Allow to choose all fields as possible
    if ($this->view->style_plugin->usesFields()) {
      $options = [];
      foreach ($this->view->display_handler->getHandlers('field') as $name => $field) {
        $definition = $field->definition;
        /** @var EntityFieldManagerInterface $entityFieldManager */
        $entityFieldManager = \Drupal::service('entity_field.manager');
        $storage = $entityFieldManager->getFieldStorageDefinitions($definition['entity_type'])[$name];
        if (empty($storage) || !is_callable([$storage, 'getThirdPartySetting'])) {
          continue;
        }
        // Check if the field is encrypted.
        $hasBlindIndex = $storage->getThirdPartySetting('field_encrypt', 'encrypt', FALSE)
          && $storage->getThirdPartySetting('field_encrypt', 'blind_index', FALSE);
        if ($hasBlindIndex) {
          $options[$name] = $field->adminLabel(TRUE);
        }
      }
      $form['operator']['#options'] = array_intersect_key($form['operator']['#options'], array_flip(['=', 'contains']));
      if ($options) {
        $form['fields'] = [
          '#type' => 'select',
          '#title' => $this->t('Choose encrypted field for filtering'),
          '#description' => $this->t("This filter doesn't work for very special field handlers."),
          '#options' => $options,
          '#default_value' => $this->options['fields'],
        ];
      }
      else {
        $form_state->setErrorByName('', $this->t('You have to add some fields to be able to use this filter.'));
      }
    }
  }

  public function query()
  {

  }

}
