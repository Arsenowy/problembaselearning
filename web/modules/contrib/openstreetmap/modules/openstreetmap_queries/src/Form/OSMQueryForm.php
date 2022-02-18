<?php

namespace Drupal\openstreetmap_queries\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the overpass query entity edit forms.
 */
class OSMQueryForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    $entity = $this->getEntity();
    $result = $entity->save();
    $link = $entity->toLink($this->t('View'))->toRenderable();

    $message_arguments = ['%label' => $this->entity->label()];
    $logger_arguments = $message_arguments + ['link' => render($link)];

    if ($result == SAVED_NEW) {
      $this->messenger()->addStatus($this->t('New overpass query %label has been created.', $message_arguments));
      $this->logger('openstreetmap_query')->notice('Created new overpass query %label', $logger_arguments);
    }
    else {
      $this->messenger()->addStatus($this->t('The overpass query %label has been updated.', $message_arguments));
      $this->logger('openstreetmap_query')->notice('Updated new overpass query %label.', $logger_arguments);
    }

    $form_state->setRedirect('entity.osm_query.canonical', ['osm_query' => $entity->id()]);
  }

}
