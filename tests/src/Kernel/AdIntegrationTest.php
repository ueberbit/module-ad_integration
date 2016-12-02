<?php

namespace Drupal\Tests\ad_integration\Kernel;

use Drupal\ad_integration\AdIntegrationLookup;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\node\Entity\NodeType;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Class AdIntegrationTest.
 *
 * @package Drupal\ad_integration\Tests\Kernel
 * @group ad_integration
 */
class AdIntegrationTest extends KernelTestBase {

  use NodeCreationTrait {
    getNodeByTitle as drupalGetNodeByTitle;
    createNode as drupalCreateNode;
  }

  public static $modules = [
    'ad_integration', 'filter', 'user', 'system', 'field', 'node', 'text', 'taxonomy',
  ];

  private $configs = [
    'adsc_unit1' => 'adscunit1',
    'adsc_unit2' => 'adscunit2',
    'adsc_unit3' => 'adscunit3',
    'adsc_mode'  => 'adscmode',
  ];

  protected $nodeType;
  protected $vocabulary;
  protected $term;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatch|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $routeMatch;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig('user');
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('taxonomy_term');
    $this->installConfig('ad_integration');
    $this->installConfig(array('filter', 'field', 'node'));

    $this->nodeType = NodeType::create(array(
      'type' => 'page',
      'name' => 'page',
    ));
    $this->nodeType->save();

    $this->vocabulary = Vocabulary::create([
      'name' => $this->randomMachineName(),
      'vid' => Unicode::strtolower($this->randomMachineName()),
      'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
    ]);
    $this->vocabulary->save();

    $config = $this->config('ad_integration.settings');
    foreach ($this->configs as $property => $value) {
      $config->set($property . '_default', $value . '_default');
    }
    $config->save();
  }

  /**
   * Tests default configs.
   */
  public function testDefaults() {
    $this->executeTestsForAllProperties(NULL, '_default', 'Default config');
  }

  /**
   * Tests the default config with a route.
   */
  public function testDefaultsWithRoute() {
    $this->addAdvertisingFieldToNode();

    $node = $this->drupalCreateNode();

    $adIntegrationLookup = $this->getAdIntegrationLookupServiceForNode($node);

    foreach ($this->configs as $property => $orignalValue) {
      $value = $adIntegrationLookup->byCurrentRoute($property);
      self::assertEquals($orignalValue . '_default', $value, 'Default with Route: value for' . $property . 'found');
    }
  }

  /**
   * Test by node overridden config with route.
   *
   * Tests if correct ad properties are returned from
   * AdIntegrationLookup service, overridden by node and using a route.
   */
  public function testOverrideByNodeWithRoute() {
    $this->addAdvertisingFieldToNode();

    $field_advertising = $this->postfixPropertyValues($this->configs, '_overrideRoute');

    /** @var Node $node */
    $node = $this->drupalCreateNode(array(
      'field_advertising' => $field_advertising,
    ));

    $adIntegrationLookup = $this->getAdIntegrationLookupServiceForNode($node);

    foreach ($this->configs as $property => $orignalValue) {
      $value = $adIntegrationLookup->byCurrentRoute($property);
      self::assertEquals($orignalValue . '_overrideRoute', $value, 'RouteOverride: value for' . $property . 'found');
    }
  }

  /**
   * Test by term overridden config with a route.
   */
  public function testOverrideByTermWithRoute() {
    $this->addAdvertisingFieldToNode();

    // Field to be added to Term.
    $this->addField('field_advertising', 'taxonomy_term', $this->vocabulary->id(), 'ad_integration_settings');

    // Field to be added to Node.
    $this->addField('field_channel', 'node', $this->nodeType->id(), 'entity_reference', array('target_type' => 'taxonomy_term'));

    $field_advertising = $this->postfixPropertyValues($this->configs, '_termoverrideRoute');

    // Create Term.
    $term = Term::create([
      'name' => $this->randomMachineName(),
      'vid' => $this->vocabulary->id(),
      'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
      'field_advertising' => $field_advertising,
    ]);
    $term->save();

    // Create Node.
    /** @var Node $node */
    $node = $this->drupalCreateNode(array(
      'field_channel' => array(
        'target_id' => $term->id(),
      ),
    ));

    $adIntegrationLookup = $this->getAdIntegrationLookupServiceForNode($node);

    foreach ($this->configs as $property => $orignalValue) {
      $value = $adIntegrationLookup->byCurrentRoute($property);
      self::assertEquals($orignalValue . '_termoverrideRoute', $value, 'RouteOverride by term: value for' . $property . 'found');
    }

    // Add subterm and test it.
    $subTerm = Term::create([
      'name' => $this->randomMachineName(),
      'vid' => $this->vocabulary->id(),
      'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
      'field_advertising' => array(),
      'parent' => array(
        'target_id' => $term->id(),
      ),
    ]);
    $subTerm->save();

    /** @var Node $node */
    $nodeWithSubTerm = $this->drupalCreateNode(array(
      'field_channel' => array(
        'target_id' => $subTerm->id(),
      ),
    ));

    $adIntegrationLookup = $this->getAdIntegrationLookupServiceForNode($nodeWithSubTerm);

    foreach ($this->configs as $property => $orignalValue) {
      $value = $adIntegrationLookup->byCurrentRoute($property);
      self::assertEquals($orignalValue . '_termoverrideRoute', $value, 'RouteOverride with empty sub term, overridden by parent term: value for' . $property . 'found');
    }

    $field_advertising = $this->postfixPropertyValues($this->configs, '_subtermoverrideRoute');

    $subTerm->field_advertising = $field_advertising;
    $subTerm->save();

    foreach ($this->configs as $property => $orignalValue) {
      $value = $adIntegrationLookup->byCurrentRoute($property);
      self::assertEquals($orignalValue . '_subtermoverrideRoute', $value, 'RouteOverride by subterm: value for' . $property . 'found');
    }
  }

  /**
   * Returns an ad_integration.lookup service, routed to a node.
   *
   * The service is provided with a mocked CurrentRoute.
   *
   * @return \Drupal\ad_integration\AdIntegrationLookup
   *   An ad_integration.lookup service
   */
  protected function getAdIntegrationLookupServiceForNode($node) {
    /** @var PHPUnit_Framework_MockObject_MockObject $routeMatchMock */
    $routeMatchMock = $this->getMockBuilder('\Drupal\Core\Routing\CurrentRouteMatch')
      ->disableOriginalConstructor()
      ->setMethods(['getParameter', 'getRouteName'])
      ->getMock();
    $routeMatchMock->expects($this->any())
      ->method('getParameter')
      /*->with('node')*/
      ->will($this->returnValueMap(array(
        array('node', $node->id()),
      )));
    $routeMatchMock->expects($this->any())
      ->method('getRouteName')
      ->willReturn('entity.node.canonical');
    $configFactory = \Drupal::service('config.factory');
    $entityTypeManager = \Drupal::service('entity_type.manager');
    /** @var AdIntegrationLookupInterface $adIntegrationLookup */
    return new AdIntegrationLookup($routeMatchMock, $configFactory, $entityTypeManager);
  }

  /**
   * Tests if correct ad properties are returned from default config.
   */
  public function testDefaultsWithEntity() {
    /** @var Node $node */
    $node = $this->drupalCreateNode();

    $this->executeTestsForAllProperties($node, '_default', 'Default config with entity');

  }

  /**
   * Tests if correct ad properties are returned, when overridden by node.
   */
  public function testOverrideByNodeWithEntity() {
    $this->addAdvertisingFieldToNode();

    $field_advertising = $this->postfixPropertyValues($this->configs, '_override');

    /** @var Node $node */
    $node = $this->drupalCreateNode(array(
      'field_advertising' => $field_advertising,
    ));

    /** @var FieldItemListInterface $field_ad */
    /*$field_ad =  $node->get('field_advertising');*/

    $this->executeTestsForAllProperties($node, '_override', 'Overridden by node');
  }

  /**
   * Tests if correct ad properties are returned, when overridden by term.
   */
  public function testOverrideByTermWithEntity() {
    $this->addAdvertisingFieldToNode();

    // Field to be added to Term.
    $this->addField('field_advertising', 'taxonomy_term', $this->vocabulary->id(), 'ad_integration_settings');

    // Field to be added to Node.
    $this->addField('field_channel', 'node', $this->nodeType->id(), 'entity_reference', array('target_type' => 'taxonomy_term'));

    $field_advertising = $this->postfixPropertyValues($this->configs, '_termoverride');

    // Create Term.
    $term = Term::create([
      'name' => $this->randomMachineName(),
      'vid' => $this->vocabulary->id(),
      'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
      'field_advertising' => $field_advertising,
    ]);
    $term->save();

    // Create Node.
    /** @var Node $node */
    $node = $this->drupalCreateNode(array(
      'field_channel' => array(
        'target_id' => $term->id(),
      ),
    ));

    $this->executeTestsForAllProperties($node, '_termoverride', 'Overridden by term');

    // Add subterm and test it.
    $subTerm = Term::create([
      'name' => $this->randomMachineName(),
      'vid' => $this->vocabulary->id(),
      'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
      'field_advertising' => array(),
      'parent' => array(
        'target_id' => $term->id(),
      ),
    ]);
    $subTerm->save();

    /** @var Node $node */
    $nodeWithSubTerm = $this->drupalCreateNode(array(
      'field_channel' => array(
        'target_id' => $subTerm->id(),
      ),
    ));

    $this->executeTestsForAllProperties($nodeWithSubTerm, '_termoverride', 'With empty sub term, overriden by parent term');

    $field_advertising = $this->postfixPropertyValues($this->configs, '_subtermoverride');

    $subTerm->field_advertising = $field_advertising;
    $subTerm->save();

    $this->executeTestsForAllProperties($nodeWithSubTerm, '_subtermoverride', 'Overridden by sub term');
  }

  /**
   * Returns new array, where values of an array are postfixed.
   *
   * @param array $arr
   *   The array, which should be postfixed.
   * @param string $postfix
   *   The postfix to be used.
   *
   * @return array
   *   The postfixed array.
   */
  protected function postfixPropertyValues($arr, $postfix) {
    $field_advertising = array();
    foreach ($arr as $property => $value) {
      $field_advertising[$property] = $value . $postfix;
    }
    return $field_advertising;
  }

  /**
   * Execute tests for all properties.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $node
   *   The entity, which should be used for the lookup.
   * @param string $postfix
   *   A postfix for the expected value.
   * @param string $prefixMessage
   *   A prefix for the assertion message.
   */
  protected function executeTestsForAllProperties(ContentEntityInterface $node = NULL, $postfix = '', $prefixMessage = '') {
    /** @var AdIntegrationInterface $adIntegration */
    $adIntegration = \Drupal::service('ad_integration');

    $data = array();
    if (isset($node)) {
      $data = array('entity' => $node);
    }

    $value = $adIntegration->getAdUnit1($data);
    self::assertEquals($this->configs['adsc_unit1'] . $postfix, $value, $prefixMessage . ': value for adsc_unit1 found');

    $value = $adIntegration->getAdUnit2($data);
    self::assertEquals($this->configs['adsc_unit2'] . $postfix, $value, $prefixMessage . ': value for adsc_unit2 found');

    $value = $adIntegration->getAdUnit3($data);
    self::assertEquals($this->configs['adsc_unit3'] . $postfix, $value, $prefixMessage . ': value for adsc_unit3 found');

    $value = $adIntegration->getAdMode($data);
    self::assertEquals($this->configs['adsc_mode'] . $postfix, $value, $prefixMessage . ': value for adsc_mode found');
  }

  /**
   * Adds an advertising field to Node.
   */
  protected function addAdvertisingFieldToNode() {
    $field_name = 'field_advertising';
    $this->addField($field_name, 'node', 'page', 'ad_integration_settings');

    $node = $this->drupalCreateNode();
    self::assertTrue($node->hasField($field_name), 'Node has field advertising');
  }

  /**
   * Helper method, to add a field to an entity type and bundle.
   *
   * @param string $field_name
   *   The field name.
   * @param string $entityType
   *   The entity type, the field should be added to.
   * @param string $bundle
   *   The bundle , the field should be added to.
   * @param string $fieldType
   *   The type of field.
   * @param array $settings
   *   Optional field type specific settings.
   */
  protected function addField($field_name, $entityType, $bundle, $fieldType, $settings = array()) {
    $field_storage = FieldStorageConfig::create(array(
      'field_name' => $field_name,
      'entity_type' => $entityType,
      'type' => $fieldType,
      'settings' => $settings,
    ));
    $field_storage->save();

    $field = FieldConfig::create(array(
      'field_storage' => $field_storage,
      'bundle' => $bundle,
    ));
    $field->save();
  }

}
