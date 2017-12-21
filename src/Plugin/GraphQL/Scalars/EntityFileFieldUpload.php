<?php

namespace Drupal\store_graph_upload\Plugin\GraphQL\Scalars;


/**
 * @GraphQLScalar(
 *   id = "entity_file_field_upload",
 *   name = "EntityFileFieldUpload",
 *   data_type = "id",
 * )
 */
class EntityFileFieldUpload extends FileUpload {

  /**
   * {@inheritdoc}
   */
  public function getFileUploadValidators() {
    return $this->getPluginDefinition()['file_upload_validators'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFileUploadDirectory() {
    return $this->getPluginDefinition()['file_upload_destination'];
  }

}
