<?php

/**
 * @file
 * Contains \Drupal\ad_integration\Form\SettingsForm.
 */

namespace Drupal\ad_integration\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Utility\Token;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a form that configures ivw settings.
 */
class SettingsForm extends ConfigFormBase {
  /**
   * The token object.
   *
   * @var Token
   */
  protected $token = array();

  /**
   * Constructs a \Drupal\ad_integration\SettingsForm object.
   *
   * @param ConfigFactoryInterface $config_factory
   *  The factory for configuration objects.
   * @param Token $token
   *  The token object.
   */
  public function __construct(ConfigFactoryInterface $config_factory, Token $token) {
    parent::__construct($config_factory);
    $this->token = $token;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('token')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ad_integration_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $settings = $this->config('ad_integration.settings');

    $form['ad_settings'] = array(
      '#type' => 'vertical_tabs',
      '#default_tab' => 'site_settings',
    );

    $form['site_settings'] = array(
      '#type' => 'details',
      '#title' => t('Site settings'),
      '#open' => TRUE,
      '#group' => 'ad_settings',
    );

    $form['default_values'] = [
      '#type' => 'details',
      '#title' => t('Default values'),
      '#open' => FALSE,
      '#group' => 'ad_settings',
    ];

    $form['default_values']['ad_rubric_default'] = [
      '#title' => t('Default Ad Rubric'),
      '#type' => 'textfield',
      '#default_value' => $settings->get('ad_rubric_default'),
      '#description' => t('Default ad rubric')
    ];

    $form['default_values']['ad_rubric_overridable'] = [
      '#type' => 'checkbox',
      '#title' => t('Ad rubric is overrideable'),
      '#default_value' => $settings->get('ad_rubric_overridable'),
    ];

    $form['default_values']['ad_ressort_default'] = [
      '#title' => t('Default Ad Ressort'),
      '#type' => 'textfield',
      '#default_value' => $settings->get('ad_ressort_default'),
      '#description' => t('Default ad rubric')
    ];

    $form['default_values']['ad_ressort_overridable'] = [
      '#type' => 'checkbox',
      '#title' => t('Ad ressort is overrideable'),
      '#default_value' => $settings->get('ad_ressort_overridable'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $values = $form_state->getValues();
    $config =$this->configFactory()->getEditable('ad_integration.settings');
    $config->set('ad_rubric_default', $values['ad_rubric_default'])
      ->set('ad_rubric_overridable', $values['ad_rubric_overridable'])
      ->set('ad_ressort_default', $values['ad_ressort_default'])
      ->set('ad_ressort_overridable', $values['ad_ressort_overridable'])
      ->save();
  }


  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'ad_integration.settings',
    ];
  }
}
