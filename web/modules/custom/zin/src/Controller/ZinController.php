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
  public function content() {
    $element = [
      '#markup' => 'Hello! You can add here a photo of your cat.',
    ];
    return $element;
  }

}