<?php

/**
 * @return
 * Contains \Drupal\zin\Controller\ZinController.
 */

namespace Drupal\zin\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for zin routes.
 */
class ZinController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\zin\Form\CatForm');
    $build['content'] = [
      '#form' => $form,      
    ];
    $build['header'] = [
      '#type' => 'markup',
      '#markup' => '<div class="process-heading">' . $this->t('Hello! You can add here a photo of your cat.') . '</div>',
    ];
    $heading = [
      'name' => t('Cat\'s name'),
      'email' => t('E-mail'),
      'image' => t('Cat\'s image'),
      'timestamp' => t('Submitting date'),
    ];
   /* $cats['table'] = [
      '#type' => 'table',
      '#header' => $heading,
      '#rows' => $this->getCatsInfo(),
    ]; */
    return [$build['header'], $form];
  }

/*  public function getCatsInfo() {
    $output = \Drupal::database()->select('zin', 'm')
      ->fields('m', ['cat_name', 'email', 'cat_image', 'timestamp'])
      ->orderBy('id', 'DESC')
      ->execute();
    $data = [];
    foreach ($output as $cat) {
      $data[] = [
        'cat_name' => $cat->name,
        'email' => $cat->email,
        'cat_image' => File::load($cat->image)->getFileUri(),
        'timestamp' => $cat->timestamp,
      ];
    }
  return $data;
  } */
}


// get data from database
//$query = \Drupal::database()->select('zin', 'm');
//$query->fields('m', ['id', 'cat_name', 'email', 'cat_image', 'date']);
