<?php
namespace Drupal\li\Form;
use Drupal\Core\Form\mysql;
use Drupal\file\Entity\File;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Messenger\Messenger;


class Liform extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
        return 'collect_phone';
  } 

 
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Your cat’s name:'),
      '#placeholder' => $this->t("min 2 ---- max 32 symbols"),
      //'#description' => $this->t('min-2 ---- max-32'),
      '#required' => TRUE
    );
    $form['email'] = [
      '#title' => 'Your email:',
      '#type' => 'email',
      '#required' => TRUE,
      '#placeholder' => $this->t('Email can only contain Latin letters, "_" or "-" '),
     // '#description' => $this->t('Email can only contain Latin letters, "_" or "-" '),
      '#ajax' => [
        'callback' => '::validateEmailAjax',
        'event' => 'change',
        'progress' => array(
          'type' => 'throbber',
          'message' => t('Verifying email..'),
        ),
      ],
      '#suffix' => '<div class="email-validation-message"></div>'
    ];
    $form['my_file'] = array(
      '#type' => 'managed_file',
      '#title' => 'Add image:',
      '#name' => 'my_custom_file',
      '#description' => $this->t('jpg, png, jpeg <br> max-size: 2MB'),
      '#required' => FALSE,
      '#upload_validators' => [
        'file_validate_is_image' => array(),
        'file_validate_extensions' => array('png jpg jpeg'),
        'file_validate_size' => array(25600000)
      ],
      '#upload_location' => 'public://images/'
    );

    
    
   
    $form['actions']['#type'] = 'actions';
   
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Add cat'),
      '#button_type' => 'primary',
      '#ajax' => [
        'event' => 'click',
        'callback' => '::submitAjax',
        'progress' => 'none',
      ],
    );
    return $form; 
  }
 
  public function submitAjax(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    if ($form_state->getErrors()) {
      foreach ($form_state->getErrors() as $err) {
        $response->addCommand(new MessageCommand(
          $err, NULL, ['type' => 'error']));
      }
      $form_state->clearErrors();
    }
    else {
      $response->addCommand(new MessageCommand(
        $this->t('Your cat added successfully.'),
        NULL,
        ['type' => 'status'],
        TRUE));
    }
    $this->messenger()->deleteAll();
    return $response;
  }



  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (strlen($form_state->getValue('name')) < 2 ) {
      $form_state->setErrorByName('name', $this->t('Name is too short.'));
    }
    if (strlen($form_state->getValue('name')) > 32){
      $form_state->setErrorByName('name', $this->t('Name is too long.'));
    }
    $My_File = $form_state->getValue('my_file');
    if (empty($My_File)) {
      $form_state->setErrorByName('my_file', $this->t('No image found'));
    } 
  }

  public function validateEmailAjax(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $input = $form_state->getValue('email');
    $regex = '/^[A-Za-z_\-]+@\w+(?:\.\w+)+$/';
    if (preg_match($regex, $input)) {
    $response->addCommand(new MessageCommand(t('Email valid')));
    }
    else {
    $response->addCommand(new MessageCommand(t('E-mail name can only contain latin characters, hyphens and underscores.'), NULL, ['type' => 'error']));
    }
    return $response;
  }
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->messenger()->addStatus('Hello) This is perfect name!' );
    $database = \Drupal::database();
    $picture = $form_state->getValue('my_file');
    $file = \Drupal\file\Entity\File::load($picture[0]);
    $file->setPermanent();
    $file->save();
    $database->insert('li')
    ->fields([
    'name' => $form_state->getValue('name'),
    'email' => $form_state->getValue('email'),
    'image' => $picture[0],
    'timestamp' => date('Y-m-d h:m:s'),
    ])
      ->execute();
  }
  
  
  }