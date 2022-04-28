<?php

namespace Drupal\li\Form;

use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;


class Liadmin extends ConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm($form, FormStateInterface $form_state): array {
    $header_title = [
      'id' => $this->t('id'),
      'name' => $this->t('Name'),
      'email' => $this->t('Email'),
      'image' => $this->t('Image'),
      'timestamp' => $this->t('Date Created'),
      'delete' => $this->t('Delete'),
      'edit' => $this->t('Edit'),
    ];
    $form['table'] = [
      '#type' => 'tableselect',
      '#header' => $header_title,
      '#options' => $this->getCats(),
      '#empty' => $this->t('There are no items.'),
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Delete'),
    ];
    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function getFormId() : string {
    return "li_admin";
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl(): Url {
    return new Url('liadmin.content');
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('You really want to delete selected cat(s)?');
  }

 
  public function getCats() : array {
    $database = \Drupal::database();
    $result = $database->select('li', 'l')
      ->fields('l', ['id', 'name', 'email', 'image', 'timestamp'])
      ->orderBy('id', 'DESC')
      ->execute();
    $cats = [];
    foreach ($result as $cat) {
      $cats[] = [
        'id' => $cat->id,
        'name' => $cat->name,
        'email' => $cat->email,
        'image' => [
            'data' => [
              '#theme' => 'image_style',
              '#style_name' => 'thumbnail',
              '#uri' => File::load($cat->image)->getFileUri(),
              '#attributes' => [
                'alt' => $cat->name,
                'title' => $cat->name,
              ],
            ],
          ],
        'timestamp' => date($cat->timestamp),
        'edit' => [
          'data' => [
            '#type' => 'link',
            '#title' => $this->t('Edit'),
            '#url' => Url::fromRoute('liedit.content', ['id' => $cat->id]),
            '#options' => [
              'attributes' => [
                'class' => 'use-ajax edit',
                'data-dialog-type' => 'modal',
                'data-dialog-options' => '{ "title":"Edit cat information"}',
              ],
            ],
          ],
        ],
        'delete' => [
          'data' => [
            '#type' => 'link',
            '#title' => $this->t('Delete'),
            '#url' => Url::fromRoute('li.content', ['id' => $cat->id]),
            '#options' => [
              'attributes' => [
                'class' => 'use-ajax delete',
                'data-dialog-type' => 'modal',
              ],
            ],
          ],
        ],
      ];
    }
    return $cats;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $rows = $form_state->getCompleteForm()['table']['#value'];
    if ($rows) {
      $id = [];
      foreach ($rows as $i) {
        $id[] = $form['table']['#options'][$i]['id'];
      }
      $database = \Drupal::database();
      $database->delete('li')
        ->condition('id', $id, 'IN')
        ->execute();
      \Drupal::messenger()->addStatus('Successfully deleted.');
    }
    else {
      $this->messenger()->addMessage($this->t("No rows selected to delete"), 'error');
    }
  }

}