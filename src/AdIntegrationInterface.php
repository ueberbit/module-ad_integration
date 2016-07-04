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
   * @return string
   *   The ad unit 1.
   */
  public function getAdUnit1();

  /**
   * Gets Ad unit 2.
   *
   * @return string
   *   The ad unit 2.
   */
  public function getAdUnit2();

  /**
   * Gets Ad unit 3.
   *
   * @return string
   *   The ad unit 3.
   */
  public function getAdUnit3();

  /**
   * Gets Ad keyword.
   *
   * @return string
   *   The ad keyword.
   */
  public function getKeyword();

  /**
   * Gets Ad mode.
   *
   * @return string
   *   The ad unit 3.
   */
  public function getAdMode();

}
