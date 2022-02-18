<?php

namespace Drupal\openstreetmap\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * {@inheritdoc}
 */
class OSMNodeTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $osm_node_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $osm_node_type->label(),
      '#description' => $this->t("Label for the OSM Node type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $osm_node_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\openstreetmap\Entity\OSMNodeType::load',
      ],
      '#disabled' => !$osm_node_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $osm_node_type = $this->entity;
    $status = $osm_node_type->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label OSM Node type.', [
          '%label' => $osm_node_type->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label OSM Node type.', [
          '%label' => $osm_node_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($osm_node_type->toUrl('collection'));
  }

}
