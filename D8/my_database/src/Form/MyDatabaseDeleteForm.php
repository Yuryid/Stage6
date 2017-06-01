<?php
/**
 * @file
 * Contains \Drupal\my_database\Form\MyDatabaseDeleteForm.
 */

namespace Drupal\my_database\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;

/**
 * Contribute form.
 */
class MyDatabaseDeleteForm extends ConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'my_database_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {

    $this->fid = $id;

    $form = parent::buildForm($form, $form_state);
    $form['actions']['submit']['#button_type'] = 'danger';
    
    return $form;
  }

  /**
   * {@inheritdoc}
   */
 public function submitForm(array &$form, FormStateInterface $form_state) {

    $txn = db_transaction();
    try {
      $query = \Drupal::database()->delete('custom_table')
              ->condition('id', $this->fid)
              ->execute();

      $form_state->setRedirect('my_database.list');
    }
    catch (Exception $e) {
      // Something went wrong somewhere, so roll back now.
      $txn->rollback();
      // Log the exception to watchdog.
      watchdog_exception('type', $e);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Delete element?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('my_database.list');
  }

  /**
   * {@inheritdoc}
   */
    public function getDescription() {
    return t('Only do this if you are sure!');
  }

  /**
   * {@inheritdoc}
   */
    public function getConfirmText() {
    return t('Delete it!');
  }
}