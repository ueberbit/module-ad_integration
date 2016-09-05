<?php

/**
 * @file
 * Builds placeholder replacement tokens for ad-related data.
 */

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\taxonomy\TermInterface;

/**
 * Implements hook_token_info().
 */
function ad_integration_token_info() {
  $type = array(
    'name' => t('Advertising'),
    'description' => t('Tokens for Advertising data.'),
    'needs-data' => 'advertising',
  );

  // Core comment tokens.
  $ad['adsc_unit1'] = array(
    'name' => t("Ad unit 1"),
    'description' => t("Ad unit 1."),
  );
  $ad['adsc_unit2'] = array(
    'name' => t("Ad unit 2"),
    'description' => t("Ad unit 2."),
  );
  $ad['adsc_unit3'] = array(
    'name' => t("Ad unit 3"),
    'description' => t("Ad unit 3."),
  );
  $ad['adsc_keyword'] = array(
    'name' => t("Ad keyword"),
    'description' => t("Komma seperated list of ad keywords."),
  );
  $ad['adsc_mode'] = array(
    'name' => t("Ad mode"),
    'description' => t("Ad mode."),
  );
  return array(
    'types' => array('advertising' => $type),
    'tokens' => array(
      'advertising' => $ad,
    ),
  );
}

/**
 * Implements hook_tokens().
 */
function ad_integration_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $sanitize = !empty($options['sanitize']);
  $replacements = array();

  if ($type == 'advertising') {

    $lookupFrom = 'currentRoute';
    if (isset($data['entity']) && $data['entity'] instanceof ContentEntityInterface) {
      $lookupFrom = 'entity';
    }
    elseif (isset($data['term']) && $data['term'] instanceof  TermInterface) {
      $lookupFrom = 'term';
    }

    /** @var \Drupal\ad_integration\AdIntegrationLookupInterface $lookup */
    $lookup = \Drupal::service('ad_integration.lookup');
    foreach ($tokens as $name => $original) {
      switch ($lookupFrom) {
        case 'currentRoute':
          $replacement = $lookup->byCurrentRoute($name);
          break;

        case 'entity':
          $replacement = $lookup->byEntity($name, $data['entity']);
          break;

        case 'term':
          $replacement = $lookup->byTerm($name, $data['term']);
          break;

        default:
          $replacement = NULL;
          break;
      }
      $replacements[$original] = $sanitize ? Xss::filter($replacement) : $replacement;
    }
  }

  return $replacements;
}
