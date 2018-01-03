<?php

namespace Drupal\graphql_file_upload\Plugin\GraphQL\InputTypes;

use Drupal\graphql\Plugin\GraphQL\InputTypes\InputTypePluginBase;

/**
 * FileInput type.
 *
 * @GraphQLInputType(
 *   id = "file_input",
 *   name = "FileInput",
 *   fields = {
 *     "filename" = "String"
 *   }
 * )
 */
class FileInput extends InputTypePluginBase {

}
