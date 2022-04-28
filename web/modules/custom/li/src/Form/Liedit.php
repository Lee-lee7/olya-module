<?php

namespace Drupal\li\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Ajax\CloseModalDialogCommand;


class Liedit extends Liform {
  /**
   *
   * @var int
   */
  protected object $cat;
 // protected int $id;
  /**
   * {@inheritdoc}
   */

  public function getFormId() : string {
    return "li_edit";
  }



  public function buildForm(array $form, FormStateInterface $form_state, int $id = NULL): array {
    $database = \Drupal::database();
    $result = $database->select('li', 'l')
      ->fields('l', ['id', 'name', 'email', 'image'])
      ->condition('id', $id)
      ->execute()->fetch();
    $this->cat = $result;
    $form = parent::buildForm($form, $form_state);
    $form['name']['#default_value'] = $result->name;
    $form['name']['#prefix'] = '<div class="error-massage-modal"></div>';
    $form['email']['#default_value'] = $result->email;
    $form['email']['#suffix'] = '<div class="email-massage-modal"></div>';
    $form['my_file']['#default_value'][] = $result->image;
    $form['submit']['#value'] = $this->t('Edit cat');
    return $form;
  }



  public function emailValidator(array &$form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();
    $input = $form_state->getValue('email');
    $regex = '/^[A-Za-z_\-]+@\w+(?:\.\w+)+$/';
    if (preg_match($regex, $input)) {
      $response->addCommand(new MessageCommand(
        $this->t('Email valid'),
        '.email-massage-modal'));
    }
    else {
      $response->addCommand(new MessageCommand(
        $this->t('E-mail name can only contain latin characters, hyphens and underscores.'),
        '.email-massage-modal', ['type' => 'error']));
    }
    if (empty($input)) {
      $response->addCommand(new RemoveCommand('.email-massage-modal .messages--error'));
    }
    return $response;
  }

  
  public function submitAjax(array &$form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();
    if ($form_state->getErrors()) {
      foreach ($form_state->getErrors() as $err) {
        $response->addCommand(new MessageCommand(
          $err, '.error-massage-modal', ['type' => 'error']));
      }
      $form_state->clearErrors();
    }
    else {
      $response->addCommand(new MessageCommand(
        $this->t('Cat information changed successfully.'),
        NULL,
        ['type' => 'status'],
        TRUE));
      $response->addCommand(new CloseModalDialogCommand());
    }
    $this->messenger()->deleteAll();
    return $response;
  } 
  

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $updated = [
      'name' => $form_state->getValue('name'),
      'email' => $form_state->getValue('email'),
      'image' => $form_state->getValue('my_file')[0],
    ];
    $database = \Drupal::database();
    $database->update('li')
      ->condition('id', $this->cat->id)
      ->fields($updated)
      ->execute();
    if ($updated['image'] != $this->cat->image) {
      $file = File::load($updated['image']);
      $file->setPermanent();
      $file->save();
      File::load($this->cat->image)->delete(); 
    }
  }
 
}
