<?php

/**
 * @file
 * Contains Drupal\ad_integration\AdIntegrationInterface
 */

namespace Drupal\ad_integration;

use Symfony\Component\DependencyInjection\ContainerInterface;

interface AdIntegrationInterface {
  /**
   * Gets Ad rubric
   *
   * @return string
   *   The ad rubric.
   */
  function getAdRubric();

  /**
   * Gets Ad ressort
   *
   * @return string
   *   The ad ressort.
   */
  function getAdRessort();
}

