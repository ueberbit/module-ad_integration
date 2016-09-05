<?php

namespace Drupal\ad_integration;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\TermInterface;

/**
 * Class AdIntegrationLookup.
 *
 * @package Drupal\ad_integration
 */
class AdIntegrationLookup implements AdIntegrationLookupInterface {

  const supportedEntityParameters = ['node', 'taxonomy_term'];

  protected $currentRouteMatch;
  protected $config;
  protected $entityTypeManager;

  /**
   * AdIntegrationLookup constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $currentRouteMatch
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  public function __construct(RouteMatchInterface $currentRouteMatch, ConfigFactoryInterface $configFactory, EntityTypeManagerInterface $entityTypeManager) {
    $this->currentRouteMatch = $currentRouteMatch;
    $this->config = $configFactory->get('ad_integration.settings');
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Automatically uses the current route to look up an Ad property.
   *
   * @param string $name
   *   The name of the Ad property to look up.
   * @param bool $termsOnly
   *   If set to TRUE, skips lookup on node settings.
   *
   * @return string
   *   The property value.
   */
  public function byCurrentRoute($name, $termsOnly = FALSE) {
    // TODO: Implement byCurrentRoute() method.
    return $this->byRoute($name, $this->currentRouteMatch, $termsOnly);
  }

  /**
   * Get Ad property by providing a route.
   *
   * @param string $name
   *   The name of the Ad property to look up.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The route matching the entity (node, term) on which to look up properties.
   * @param bool $termsOnly
   *   If set to TRUE, skips lookup on node settings.
   *
   * @return string
   *   The property value.
   */
  public function byRoute($name, RouteMatchInterface $routeMatch, $termsOnly = FALSE) {
    $entity = NULL;

    foreach (static::supportedEntityParameters as $parameter) {
      if ($entity = $routeMatch->getParameter($parameter)) {
        if (is_numeric($entity)) {
          $entity = Node::load($entity);
        }
        $setting = $this->searchEntity($name, $entity, $termsOnly);
        if ($setting !== NULL) {
          return $setting;
        }
      }
    }

    return $this->defaults($name);
  }

  /**
   * Get Ad property by providing an entity.
   *
   * @param string $name
   *   The name of the Ad property to look up.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity (usually node) to look up the property on.
   * @param bool $termsOnly
   *   If set to TRUE, skips lookup on node settings.
   *
   * @return string
   *   The property value.
   */
  public function byEntity($name, ContentEntityInterface $entity, $termsOnly = FALSE) {
    $result = $this->searchEntity($name, $entity, $termsOnly);
    return $result !== NULL ? $result : $this->defaults($name);
  }

  /**
   * Get Ad property by providing a term.
   *
   * @param string $name
   *   The name of the Ad property to look up.
   * @param \Drupal\taxonomy\TermInterface $term
   *   The term to look up the property on.
   *
   * @return string
   *   The property value.
   */
  public function byTerm($name, TermInterface $term) {
    $result = $this->searchTerm($name, $term);
    return $result !== NULL ? $result : $this->defaults($name);
  }

  /**
   * Search for an Ad property in an entity.
   *
   * @param string $name
   *   The name of the Ad property to look up.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The content entity (usually node) to look up the property on.
   * @param bool $termsOnly
   *   If set to TRUE, skips lookup on node settings.
   *
   * @return string|null
   *   The property value or null.
   */
  protected function searchEntity($name, ContentEntityInterface $entity, $termsOnly = FALSE) {
    // Search for ad_integration_settings field.
    foreach ($entity->getFieldDefinitions() as $fieldDefinition) {
      $fieldType = $fieldDefinition->getType();

      if (!$termsOnly) {
        // If settings are found, check if an overridden value for the
        // given setting is found and return that.
        $overiddenSetting = $this->getOverriddenAdSetting($name, $fieldDefinition, $entity);
        if (isset($overiddenSetting)) {
          return $overiddenSetting;
        }
      }


      // Check for fallback categories if no ad_integration_setting is found.
      if (!isset($termOverride) && $fieldType === 'entity_reference' && $fieldDefinition->getSetting('target_type') === 'taxonomy_term') {
        $fieldName = $fieldDefinition->getName();
        if ($tid = $entity->$fieldName->target_id) {
          if ($term = Term::load($tid)) {
            $termOverride = $this->searchTerm($name, $term);
          }
        }
      }
    }

    // If we not returned before, it is possible,
    // that we found a termOverride.
    if (isset($termOverride)) {
      return $termOverride;
    }

    return NULL;
  }

  /**
   * @param string $name
   * @param \Drupal\taxonomy\Entity\TermInterface $term
   * @return string|null
   */
  protected function searchTerm($name, TermInterface $term) {
    foreach ($term->getFieldDefinitions() as $fieldDefinition) {
      $override = $this->getOverriddenAdSetting($name, $fieldDefinition, $term);
      if (isset($override)) {
        return $override;
      }
    }

    $termStorage = $this->entityTypeManager->getStorage('taxonomy_term');

    foreach ($termStorage->loadParents($term->id()) as $parent) {
      $override = $this->searchTerm($name, $parent);
      if (isset($override)) {
        return $override;
      }
    }

    return NULL;
  }

  /**
   * @param $name
   * @param $fieldDefinition
   * @param $entity
   *
   * @return string|null
   */
  protected function getOverriddenAdSetting($name, FieldDefinitionInterface $fieldDefinition, ContentEntityInterface $entity) {
    if ($fieldDefinition->getType() === 'ad_integration_settings') {
      $fieldName = $fieldDefinition->getName();
      if (!empty($entity->$fieldName->get(0)->$name)) {
        return $entity->$fieldName->get(0)->$name;
      }
    }
    return NULL;
  }

  /**
   * Get default value for Ad property.
   *
   * @param string $name
   *   The name of the Ad property to look up.
   *
   * @return string|null
   *   The default property value or NULL.
   */
  private function defaults($name) {
    return $this->config->get($name . '_default');
  }
}