<?php

namespace Drupal\Tests\custom_graphql_file_upload\Kernel;

use Drupal\Tests\graphql\Kernel\GraphQLFileTestBase;

use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

// use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\Tests\TestFileCreationTrait;

use Drupal\media_entity\Entity\Media;
use Drupal\media_entity\Entity\MediaBundle;


/**
 * Test a simple mutation.
 *
 * @group graphql
 */
class MutationFileUploadTest extends GraphQLFileTestBase {

//  use UserCreationTrait {
//    createRole as drupalCreateRole;
//    createUser as drupalCreateUser;
//  }

  use TestFileCreationTrait {
    getTestFiles as drupalGetTestFiles;
  }

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'entity',
    'image',
    'user',
    'field',
    'system',
    'file',
    'graphql',
    'graphql_core',
    'custom_graphql_file_upload',
//    'custom_graphql_field',
    'simpletest',
//    'media',
//    'media_entity',
//    'media_entity_image',
  ];

  /**
   * The test media bundle.
   *
   * @var \Drupal\media_entity\MediaBundleInterface
   */
  protected $testBundle;


  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('file');
    $this->installSchema('file', 'file_usage');
//    $this->installEntitySchema('media');
    $this->installConfig(['field', 'system', 'image', 'file']);

    Role::load(RoleInterface::AUTHENTICATED_ID)
      ->grantPermission('execute graphql requests')
      ->grantPermission('create media')
      ->grantPermission('view media')
      ->grantPermission('access content')
      ->save();

  }

  /**
   * Get the path to the directory containing test query files.
   *
   * @return string
   *   The path to the collection of test query files.
   */
  protected function gettestsDirectory() {
    return drupal_get_path('module', explode('\\', get_class($this))[2]) . '/tests/';
  }

  /**
   * Test that the breadcrumb query returns breadcrumbs for given path.
   */
//  public function testCustomField() {
//    $result = $this->executeQueryFile('currentTime.gql');
//    $this->assertEquals(date('Y-m-d H:ia'), $result['data']['currentTime']);
//  }

  /**
   * Test if the file is uploaded properly.
   */
  public function testMutationFileUploadQuery() {

//    $path = $this->gettestsDirectory() . '/bob_ross.jpg';
//    $type = pathinfo($path, PATHINFO_EXTENSION);
//    $data = file_get_contents($path);

    $image = current($this->drupalGetTestFiles('image'));

//    /** @var \Drupal\media_entity\MediaBundleInterface $bundle */
//    $bundle = \Drupal::entityTypeManager()
//      ->getStorage('media_bundle')
//      ->load($this->configuration['media_bundle']);
//
//    $images = [];


    // STEP 1 : JUST FOCUS ON ADDING AN IMAGE




    $result = $this->executeQueryFile('uploadFile.gql', ['file' => ['file' => $image]]);
    $this->assertEquals("HELLO", $result['data']['uploadFile']);
  }

}
