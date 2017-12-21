<?php

namespace Drupal\custom_graphql_file_upload\Plugin\GraphQL\Mutations;

use Drupal\graphql\Annotation\GraphQLMutation;
use Drupal\graphql\GraphQL\Type\InputObjectType;
use Drupal\graphql_core\Plugin\GraphQL\Mutations\Entity\CreateEntityBase;

use Youshido\GraphQL\Execution\ResolveInfo;


/**
 *  A File Upload mutation.
 *
 * @GraphQLMutation(
 *   id = "upload_file",
 *   entity_type = "file",
 *   entity_bundle = "media",
 *   secure = true,
 *   name = "uploadFile",
 *   type = "EntityCrudOutput",
 *   arguments = {
 *     "input" = "FileInput"
 *   }
 * )
 */
class UploadFile extends CreateEntityBase {


  /**
   * {@inheritdoc}
   */
  protected function extractEntityInput(array $inputArgs, InputObjectType $inputType, ResolveInfo $info) {
    return [
      'filename' => $inputArgs['filename'],
      'file' => $inputArgs['file']
    ];
  }

}