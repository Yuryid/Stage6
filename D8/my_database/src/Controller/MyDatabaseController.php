<?php
/**
 * @file
 * Contains \Drupal\my_database\Controller\MyDatabaseController.
 */

namespace Drupal\my_database\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\my_database\Form;
use Drupal\Core\Url;
use Drupal\Core\Render\RendererInterface;

class MyDatabaseController extends ControllerBase {

	/**
	* Display the add form.
	*
	*/
  public function list() {
    $txn = db_transaction();
    try {
      $result = \Drupal::database()->select('custom_table', 'tbl')
        ->fields('tbl', array('id', 'number', 'teaser','text'))
        ->orderBy('tbl.id', 'ASC')
        ->execute();
    }
    catch (Exception $e) {
      // Something went wrong somewhere, so roll back now.
      $txn->rollback();
      // Log the exception to watchdog.
      watchdog_exception('type', $e);
    }
    $content = array();

    $content['message'] = array(
      '#markup' => $this->t('List of all elements in table.'),
    );

    $rows = array();
    $headers = array(t('Id'), t('Number'), t('Teaser'), t('Text'), t('Actions'));

    foreach ($result as $row) {
      // Sanitize each entry.
      $rez = array_map('Drupal\Component\Utility\SafeMarkup::checkPlain', (array) $row);
      $t = array(
        '#type' => 'container',
      );
      $t[0][0] = array(
        '#title' => $this->t('edit '),
        '#type' => 'link',
        '#url' => Url::fromRoute('my_database.update', ['id' => $row->id]),
      );
      $t[0][1] = array(
        '#title' => $this->t(' delete'),
        '#type' => 'link',
        '#url' => Url::fromRoute('my_database.delete',['id' => $row->id]),
      );
      $rez['actions'] = \Drupal::service('renderer')->render($t);
      $rows[] = $rez;
    }

    $content['table'] = array(
      '#type' => 'table',
      '#header' => $headers,
      '#rows' => $rows,
      '#empty' => t('No entries available.'),
    );

    // Don't cache this page.
    $content['#cache']['max-age'] = 0;

    return $content;
  }
}