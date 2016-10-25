<?php

namespace Drupal\ad_integration;

use Drupal\Core\Cache\CacheableDependencyInterface;

/**
 * Interface AdIntegrationInterface.
 *
 * @package Drupal\ad_integration
 */
interface AdIntegrationInterface extends CacheableDependencyInterface {

  /**
   * Gets ad provider.
   *
   * @return string
   *   The ad provider id.
   */
  public function getAdProvider();

  /**
   * Gets ad container tag url.
   *
   * @return string
   *   The url to the container tag.
   */
  public function getAdContainerTag();

  /**
   * Gets ad engine url.
   *
   * @return string
   *   The url to the ad engine.
   */
  public function getAdEngine();

  /**
   * Gets Ad unit 1.
   *
   * @param array $data
   *   Specify an entity or a term, which should be looked up on
   *   Leave empty, to look up accorsing to current route.
   *   ['entity'] ContentEntityInterface - instance of entity.
   *   ['term'] TermInterface - instance of term.
   *
   * @return string
   *   The ad unit 1.
   */
  public function getAdUnit1($data = array());

  /**
   * Gets Ad unit 2.
   *
   * @param array $data
   *   Specify an entity or a term, which should be looked up on
   *   Leave empty, to look up accorsing to current route.
   *   ['entity'] ContentEntityInterface - instance of entity.
   *   ['term'] TermInterface - instance of term.
   *
   * @return string
   *   The ad unit 2.
   */
  public function getAdUnit2($data = array());

  /**
   * Gets Ad unit 3.
   *
   * @param array $data
   *   Specify an entity or a term, which should be looked up on
   *   Leave empty, to look up accorsing to current route.
   *   ['entity'] ContentEntityInterface - instance of entity.
   *   ['term'] TermInterface - instance of term.
   *
   * @return string
   *   The ad unit 3.
   */
  public function getAdUnit3($data = array());

  /**
   * Gets Ad keyword.
   *
   * @param array $data
   *   Specify an entity or a term, which should be looked up on
   *   Leave empty, to look up accorsing to current route.
   *   ['entity'] ContentEntityInterface - instance of entity.
   *   ['term'] TermInterface - instance of term.
   *
   * @return string
   *   The ad keyword.
   */
  public function getKeyword($data = array());

  /**
   * Gets Ad mode.
   *
   * @param array $data
   *   Specify an entity or a term, which should be looked up on
   *   Leave empty, to look up accorsing to current route.
   *   ['entity'] ContentEntityInterface - instance of entity.
   *   ['term'] TermInterface - instance of term.
   *
   * @return string
   *   The ad unit 3.
   */
  public function getAdMode($data = array());

}
