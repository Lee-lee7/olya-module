<?php
/**
 * @file
 * Contains \Drupal\hello_world\Controller\HelloController.
 */
namespace Drupal\li\Controller;

use Drupal\file\Entity\File;
use Drupal\Core\Controller\ControllerBase;

class HelloController extends ControllerBase {


  public function build(): array {
    $form = \Drupal::formBuilder()->getForm('Drupal\li\Form\Liform');
  
    $header_title = [
      'name' => t('Name'),
      'email' => t('Email'),
      'image' => t('Image'),
      'timestamp' => t('Date and time'),
    ];
    $cats['table'] = [
      '#type' => 'table',
      '#header' => $header_title,
      '#rows' => $this->getCats(),
    ];
    $build['content'] = [
      '#form' => $form,
      '#theme' => 'li-theme',
      '#cats' => $cats,
      '#text' => $this
        ->t('Hello! You can add here a photo of your cat.'),
    ]; 
    return $build;
  }

  
  public function getCats(): array {
    $database = \Drupal::database();
    $result = $database->select('li', 'l')
      ->fields('l', ['id','name', 'email', 'image', 'timestamp'])
      ->orderBy('id', 'DESC')
      ->execute();
    $cats = [];
    foreach ($result as $cat) {
      $cats[] = [
        'id' => $cat->id,
        'name' => $cat->name,
        'email' => $cat->email,
        'image' => File::load($cat->image)->createFileUrl(),
        'timestamp' => $cat->timestamp,
      ];
    }
    return $cats;
  }

}


