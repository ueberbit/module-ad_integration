<?php

namespace Drupal\ad_integration;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Interface AdIntegrationLookupInterface.
 *
 * @package Drupal\ad_integration
 */
interface AdIntegrationLookupInterface {

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
  public function byCurrentRoute($name, $termsOnly = FALSE);

  /**
   * Get Ad property by providing a route.
   *
   * @param string $name
   *   The name of the Ad property to look up.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The route matching the entity (node, term) on which to look up
   *   properties.
   * @param bool $termsOnly
   *   If set to TRUE, skips lookup on node settings.
   *
   * @return string
   *   The property value.
   */
  public function byRoute($name, RouteMatchInterface $routeMatch, $termsOnly = FALSE);

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
  public function byEntity($name, ContentEntityInterface $entity, $termsOnly = FALSE);

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
  public function byTerm($name, TermInterface $term);

}
