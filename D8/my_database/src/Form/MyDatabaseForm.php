<?php
/**
 * @file
 * Contains \Drupal\my_database\Form\MyDatabaseForm.
 */

namespace Drupal\my_database\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;

/**
 * Contribute form.
 */
class MyDatabaseForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'my_database_add_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['number'] = array(
      '#type' => 'textfield',
      '#title' => t('Number'),
      '#required' => TRUE,
    );
    $form['teaser'] = array(
      '#type' => 'textfield',
      '#title' => t('Teaser'),
      '#required' => TRUE,
    );
    $form['text'] = array(
      '#type' => 'textarea',
      '#title' => t('Text'),
      '#required' => TRUE,
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => 'Submit',
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
      $query = \Drupal::database()->insert('custom_table');
      $query->fields([
        'number',
        'teaser',
        'text',
      ]);
      $query->values([
        $form_state->getValue('number'),
        $form_state->getValue('teaser'),
        $form_state->getValue('text'),
      ]);
      $query->execute();
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