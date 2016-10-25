<?php

namespace Drupal\ad_integration\Plugin\Block;

use Drupal\Core\Cache\Cache;
use Drupal\ad_integration\AdIntegrationInterface;
use Drupal\Component\Utility\Crypt;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Advertising slot.
 *
 * @Block(
 *   id = "ad_integration_advertising_slot",
 *   admin_label = @Translation("Advertising slot"),
 * )
 */
class AdvertisingSlot extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The config factory.
   *
   * @var ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The config factory.
   *
   * @var AdIntegrationInterface
   */
  protected $adIntegration;

  /**
   * Constructs an advertising slot object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param AdIntegrationInterface $ad_integration
   *   The ad integration service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $config_factory,
    AdIntegrationInterface $ad_integration
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->adIntegration = $ad_integration;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('config.factory'), $container->get('ad_integration'));
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'adtype' => 'iframe',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    /*
     * TODO: turn textfield into select list from configurable ad tags
     */
    if ($mappings = $this->getDeviceMappings()) {
      foreach ($mappings as $mapping) {
        $device = $mapping['device'];
        $form[$device] = [
          '#type' => 'textfield',
          '#title' => $this->t('Adtag for :device', array(':device' => $device)),
          '#default_value' => $config[$device] ? $config[$device] : NULL,
        ];
      }
    }
    else {
      $form['adtag'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Adtag'),
        '#default_value' => $config['adtag'] ? $config['adtag'] : NULL,
      ];
    }

    $form['adtype'] = [
      '#type' => 'radios',
      '#title' => $this->t('Ad type'),
      '#required' => TRUE,
      '#default_value' => $config['adtype'] ? $config['adtype'] : 'iframe',
      '#options' => array('inline' => $this->t('Inline'), 'iframe' => $this->t('IFrame')),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    // Save our custom settings when the form is submitted.
    if ($mappings = $this->getDeviceMappings()) {
      foreach ($mappings as $mapping) {
        $device = $mapping['device'];
        $this->setConfigurationValue($device, $form_state->getValue($device));
      }
    }
    else {
      $this->setConfigurationValue('adtag', $form_state->getValue('adtag'));
    }
    $this->setConfigurationValue('adtype', $form_state->getValue('adtype'));
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $html_id = 'ad-slot--' . Crypt::randomBytesBase64(8);
    $ad_provider = $this->adIntegration->getAdProvider();

    $render = [
      '#theme' => 'ad_slot_' . $config['adtype'],
      '#html_id' => $html_id,
      '#ad_tag' => array(),
      '#cache' => [
        'contexts' => ['url.path'],
        'tags' => $this->adIntegration->getCacheTags(),
        'max-age' => Cache::PERMANENT,
      ],
    ];

    $attachments = [$html_id => []];

    if ($mappings = $this->getDeviceMappings()) {
      foreach ($mappings as $mapping) {
        $device = $mapping['device'];
        $attachments[$html_id][$device] = $config[$device];
        $render['#ad_tag'][$device] = $config[$device];
      }
    }
    else {
      $attachments[$html_id]['adtag'] = $config['adtag'];
      $render['#ad_tags']['desktop'] = $config['adtag'];
    }

    $render['#attached']['drupalSettings']['AdvertisingSlots'] = $attachments;
    $render['#attached']['drupalSettings']['AdProvider'] = $ad_provider;

    return $render;
  }

  /**
   * Get Device Mappings.
   *
   * @return mixed
   *   Returns mappings, if not found returns FALSE.
   */
  private function getDeviceMappings() {
    if ($config = $this->configFactory->get('breakpoint_js_settings.settings')) {
      if ($mappings = $config->get('device_mappings')) {
        return $mappings;
      }
    }
    return FALSE;
  }

}
