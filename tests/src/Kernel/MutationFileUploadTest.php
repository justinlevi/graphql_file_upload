<?php

namespace Drupal\Tests\custom_graphql_file_upload\Kernel;

use Drupal\Core\Entity\EntityFieldManager;
use Drupal\Tests\graphql\Kernel\GraphQLFileTestBase;
use Drupal\Core\Entity\ContentEntityType;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;


/**
 * Test a simple mutation.
 *
 * @group graphql
 */
class MutationFileUploadTest extends GraphQLFileTestBase {

  use UserCreationTrait {
    createRole as drupalCreateRole;
    createUser as drupalCreateUser;
  }

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'path',
    'user',
    'file',
    'file_test',
    'node',
    'field',
    'media_entity',
    'entity',
    'image',
    'graphql',
    'graphql_core',
    'custom_graphql_file_upload',
    'custom_graphql_field'
  ];


  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig('system');
    $this->installConfig('graphql');
    $this->installConfig('user');
    $this->installEntitySchema('user');


    Role::load(RoleInterface::AUTHENTICATED_ID)
      ->grantPermission('execute graphql requests')
      ->grantPermission('create media')
      ->grantPermission('view media')
      ->grantPermission('access content')
      ->grantPermission('bypass node access')
      ->grantPermission('create url aliases')
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

    $path = $this->gettestsDirectory() . '/bob_ross.jpg';
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);

    $result = $this->executeQueryFile('uploadFile.gql', ['file' => ['file' => $data]]);
    $this->assertEquals("HELLO", $result['data']['uploadFile']);
  }

}
