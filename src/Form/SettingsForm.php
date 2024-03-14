<?php

declare(strict_types = 1);

namespace Drupal\lcm_acs_xrm\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure lcm_acs_xrm settings for this site.
 */
final class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'lcm_acs_xrm_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['lcm_acs_xrm.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    // Campaign studio MC.
    $form['api'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Mautic instance'),
      '#attributes' => ['class' => ['fieldset-cs']],
      '#open' => FALSE,
    ];
    // Base URL.
    $form['api']['lcm_acs_xrm_api_baseurl'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base URL'),
      '#default_value' => $this->config('lcm_acs_xrm.settings')->get('lcm_acs_xrm_api_baseurl'),
    ];
    // Client ID.
    $form['api']['lcm_acs_xrm_api_clientid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client ID'),
      '#default_value' => $this->config('lcm_acs_xrm.settings')->get('lcm_acs_xrm_api_clientid'),
    ];
    // Secret key.
    $form['api']['lcm_acs_xrm_api_secretkey'] = [
      '#type' => 'key_select',
      '#title' => $this->t('Secret key'),
      '#default_value' => $this->config('lcm_acs_xrm.settings')->get('lcm_acs_xrm_api_secretkey'),
    ];
    // Callback URL.
    $form['api']['lcm_acs_xrm_api_callback'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Callback URL'),
      '#default_value' => $this->config('lcm_acs_xrm.settings')->get('lcm_acs_xrm_api_callback'),
    ];

    // SFTP.
    $form['sftp'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('SFTP access'),
      '#open' => TRUE,
    ];
    // Hostname.
    $form['sftp']['lcm_acs_xrm_sftp_hostname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Hostname'),
      '#default_value' => $this->config('lcm_acs_xrm.settings')->get('lcm_acs_xrm_sftp_hostname'),
    ];
    // Username.
    $form['sftp']['lcm_acs_xrm_sftp_username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#default_value' => $this->config('lcm_acs_xrm.settings')->get('lcm_acs_xrm_sftp_username'),
    ];
    // Password.
    $form['sftp']['lcm_acs_xrm_sftp_password'] = [
      '#type' => 'key_select',
      '#title' => $this->t('Password'),
      '#default_value' => $this->config('lcm_acs_xrm.settings')->get('lcm_acs_xrm_sftp_password'),
    ];
    // Port.
    $form['sftp']['lcm_acs_xrm_sftp_port'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Port'),
      '#default_value' => $this->config('lcm_acs_xrm.settings')->get('lcm_acs_xrm_sftp_port'),
    ];

    $form['#attached']['library'][] = 'lcm_acs_xrm/lcm_acs_xrm';
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // @todo Validate the form here.
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // Save configuration settings.
    $this->config('lcm_acs_xrm.settings')
      ->set('lcm_acs_xrm_api_baseurl', $form_state->getValue('lcm_acs_xrm_api_baseurl'))
      ->set('lcm_acs_xrm_api_clientid', $form_state->getValue('lcm_acs_xrm_api_clientid'))
      ->set('lcm_acs_xrm_api_secretkey', $form_state->getValue('lcm_acs_xrm_api_secretkey'))
      ->set('lcm_acs_xrm_api_callback', $form_state->getValue('lcm_acs_xrm_api_callback'))
      ->set('lcm_acs_xrm_sftp_hostname', $form_state->getValue('lcm_acs_xrm_sftp_hostname'))
      ->set('lcm_acs_xrm_sftp_username', $form_state->getValue('lcm_acs_xrm_sftp_username'))
      ->set('lcm_acs_xrm_sftp_password', $form_state->getValue('lcm_acs_xrm_sftp_password'))
      ->set('lcm_acs_xrm_sftp_port', $form_state->getValue('lcm_acs_xrm_sftp_port'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
