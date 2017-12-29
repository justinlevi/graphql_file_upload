<?php

namespace Drupal\custom_graphql_file_upload\Plugin\GraphQL\InputTypes;

use Drupal\graphql\Plugin\GraphQL\InputTypes\InputTypePluginBase;

/**
 * FileInput type.
 *
 * @GraphQLInputType(
 *   id = "file_input",
 *   name = "FileInput",
 *   fields = {
 *     "file" = "String",
 *     "files" = "String"
 *   }
 * )
 */
class FileInput extends InputTypePluginBase {

}
