<?php

/**
 * @file
 * Contains \Drupal\ad_integration\Plugin\Field\Widget\AdSettingsWidget.
 */

namespace Drupal\ad_integration\Plugin\Field\FieldWidget;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'ad_integration_settings' widget.
 *
 * @FieldWidget(
 *   id = "ad_integration_widget",
 *   module = "ad_integration",
 *   label = @Translation("Advertising Settings"),
 *   field_types = {
 *     "ad_integration_settings"
 *   }
 * )
 */
class AdSettingsWidget extends WidgetBase implements ContainerFactoryPluginInterface {
  /**
   * The config factory.
   *
   * @var ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    array $third_party_settings,
    ConfigFactoryInterface $config_factory
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->configFactory = $config_factory;
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
    return new static($plugin_id, $plugin_definition, $configuration['field_definition'], $configuration['settings'], $configuration['third_party_settings'], $container->get('config.factory'));
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(
    FieldItemListInterface $items,
    $delta,
    array $element,
    array &$form,
    FormStateInterface $form_state
  ) {
    $settings = $this->configFactory->get('ad_integration.settings');

    if ($settings->get('ad_rubric_overridable')) {
      $element['ad_rubric'] = array(
        '#type' => 'textfield',
        '#title' => t('Ad Rubric'),
        '#default_value' => isset($items[$delta]->ad_rubric) ? $items[$delta]->ad_rubric : NULL,
        '#required' => FALSE,
        '#empty_option' => t('Site default value')
      );
    }

    if ($settings->get('ad_ressort_overridable')) {
      $element['ad_ressort'] = array(
        '#type' => 'textfield',
        '#title' => t('Ad Ressort'),
        '#default_value' => isset($items[$delta]->ad_ressort) ? $items[$delta]->ad_ressort : NULL,
        '#required' => FALSE,
        '#empty_option' => t('Site default value')
      );
    }
    return $element;
  }
}
