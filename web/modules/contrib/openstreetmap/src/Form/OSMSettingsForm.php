<?php

namespace Drupal\openstreetmap\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Config form. {@inheritDoc}.
 *
 * @package Drupal\openstreetmap
 */
class OSMSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'openstreetmap_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'openstreetmap.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('openstreetmap.settings');

    $form['endpoint'] = [
      '#title' => $this->t('Interpreter Endpoint'),
      '#type' => 'textfield',
      '#default_value' => $config->get('endpoint'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $settings = $this->configFactory->getEditable('openstreetmap.settings');
    $settings->set('endpoint', $form_state->getValue('endpoint'));
    $settings->save();
  }

}
