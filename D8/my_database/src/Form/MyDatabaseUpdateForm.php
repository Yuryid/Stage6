<?php
/**
 * @file
 * Contains \Drupal\my_database\Form\MyDatabaseUpdateForm.
 */

namespace Drupal\my_database\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;

/**
 * Contribute form.
 */
class MyDatabaseUpdateForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'my_database_update_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    $txn = db_transaction();
    try {
      $result = \Drupal::database()->select('custom_table', 'tbl')
        ->fields('tbl')
        ->condition('id', $id)
        ->execute()->fetchAssoc();
    }
    catch (Exception $e) {
      // Something went wrong somewhere, so roll back now.
      $txn->rollback();
      // Log the exception to watchdog.
      watchdog_exception('type', $e);
    }
    $form['id'] = array(
      '#type' => 'value',
      '#value' => $id,
    );
    $form['number'] = array(
      '#type' => 'textfield',
      '#title' => t('Number'),
      '#default_value' => $result['number'],
      '#required' => TRUE,
    );
    $form['teaser'] = array(
      '#type' => 'textfield',
      '#title' => t('Teaser'),
      '#default_value' => $result['teaser'],
      '#required' => TRUE,
    );
    $form['text'] = array(
      '#type' => 'textarea',
      '#title' => t('Text'),
      '#default_value' => $result['text'],
      '#required' => TRUE,
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Update',
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (strlen($form_state->getValue('teaser')) < 3) {
      $form_state->setErrorByName('teaser', $this->t('The teaser is too short. Please enter bigger teaser.'));
    }
    if (strlen($form_state->getValue('text')) < 3) {
      $form_state->setErrorByName('text', $this->t('The text is too short. Please enter longer text.'));
    }
    if(!intval($form_state->getValue('number'))) {
      $form_state->setErrorByName('number', $this->t('Wrong number.'));
    }
  }

  /**
   * {@inheritdoc}
   */
 public function submitForm(array &$form, FormStateInterface $form_state) {
    $txn = db_transaction();
    try {
      $query = \Drupal::database()->update('custom_table')
              ->fields([
                'number' => $form_state->getValue('number'),
                'teaser'=> $form_state->getValue('teaser'),
                'text'=> $form_state->getValue('text'),
              ])
              ->condition('id', $form_state->getValue('id'))
              ->execute();
    }
    catch (Exception $e) {
      // Something went wrong somewhere, so roll back now.
      $txn->rollback();
      // Log the exception to watchdog.
      watchdog_exception('type', $e);
    }
    $form_state->setRedirect('my_database.list');
  }
}