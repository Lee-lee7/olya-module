<?php

/**
 * @file
 * Contains \Drupal\zin\Form\CatForm.
 */

namespace Drupal\zin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\file\Entity\File;

class CatForm extends FormBase {
  
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['container'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'box-container'],
    ];
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Your cat\'s name:'),
      '#maxlength' => 32,
      '#description' => $this->t('Note that the name of your cat must be at least 2 characters in length. The maximum length of the field is 32 characters'),
      '#required' => TRUE,
    ];
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this -> t('Your e-mail:'),
      '#description' => $this -> t('Please use only Latin letters, underscores or hyphens'),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::validateEmailAjax',
        'event' => 'keyup',
        'progress' => [
          'type' => 'throbber',
          'message' => t('Veryfying e-mail..'),
        ],
      ],
      '#suffix' => '<div class="email-validation-message"></div>'
    ];
    $form['image'] = [
      '#type' => 'managed_file',
      '#title' => $this -> t('Upload image:'),
      '#name' => 'image',
      '#description' => $this->t('Only JPG, PNG and JPEG files are allowed. Size limit is 2MB'),
      '#required' => TRUE,
      '#upload_validators' => [
        'file_validate_extensions' => array('png jpg jpeg'),
        'file_validate_size' => array(25600000),
      ],
      '#upload_location' => 'public://images/'
    ];
   
    $form['actions']['#type'] = 'actions';
   
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add cat'),
      '#button_type' => 'primary',
      '#ajax' => [
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => t('Adding the cat\'s name..'),
        ],
        'callback' => '::submitAjax',
        'wrapper' => 'box-container',
      ],
      
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'zin_catform_form';
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (strlen($form_state->getValue('name')) < 2) {
      $form_state->setErrorByName('name', $this->t('Name of the cat is too short.'));
    }
    if (strlen($form_state->getValue('name')) > 32) {
      $form_state->setErrorByName('name', $this->t('Name of the cat is too long.'));
    }  
    $my_file = $form_state->getValue('image');
    if (empty($my_file)) {
      $form_state->setErrorByName('image', $this->t('No image found'));
    } 
  }

  protected function validateEmail(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('email');
    $stableExpression = '/^[A-Za-z_\-]+@\w+(?:\.\w+)+$/';
    if (preg_match($stableExpression, $email)){
      return TRUE;
    }
    return FALSE;
  }

  /**
  * {@inheritdoc}
  */
  public function validateEmailAjax(array &$form, FormStateInterface $form_state) {
    $valid = $this->validateEmail($form, $form_state);
    $response = new AjaxResponse();
    if ($valid) {
      $css = ['box-shadow' => 'inset 0 0 10px green'];
      $message = $this->t('E-mail is valid.');
    }
    else {
      $css = ['box-shadow' => 'inset 0 0 10px red'];
      $message = $this->t('E-mail is not valid.');
    }
    $response->addCommand(new CssCommand('#edit-email', $css));
    $response->addCommand(new HtmlCommand('.email-validation-message', $message));
    return $response;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $image = $form_state->getValue('image');
    //If the image is uploaded, save it in the database
    if ($image) {
        $file = File::load($image[0]);
        $file->setPermanent();
        $file->save();
    }
    // Database connection
    $database = \Drupal::database();
    $database->insert('zin')
    ->fields([
    'name' => $form_state->getValue('name'),
    'email' => $form_state->getValue('email'),
    'image' => $image[0],

    ])
      ->execute();
  }
    // Submit form with Ajax.
    public function submitAjax(array &$form, FormStateInterface $form_state): AjaxResponse {
      $response = new AjaxResponse();
      // Modal message about errors in form
      if ($form_state->getErrors()) {
        foreach ($form_state->getErrors() as $err) {
          $response->addCommand(new MessageCommand($err, NULL, ['type' => 'error']));
        }
        $form_state->clearErrors();
      }
      // Modal message about successful data save.
      else {
        $response->addCommand(new MessageCommand($this->t('Name of the cat was added!'), NULL, ['type' => 'status'], TRUE));
        $form_state->setRebuild(TRUE);
      }
      $this->messenger()->deleteAll();
      return $response;
    }

}