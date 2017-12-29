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
 *   entity_type = "media",
 *   entity_bundle = "image",
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
      'file' => $inputArgs['file']
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {


    return $args['input']['file'];


    $entityTypeId = $this->pluginDefinition['entity_type'];
    $bundleName = $this->pluginDefinition['entity_bundle'];
    $bundleKey = $this->entityTypeManager->getDefinition($entityTypeId)->getKey('bundle');
    $storage = $this->entityTypeManager->getStorage($entityTypeId);

    // The raw input needs to be converted to use the proper field and property
    // keys because we usually convert them to camel case when adding them to
    // the schema.
    $inputArgs = $args['input'];
    /** @var \Youshido\GraphQL\Type\Object\AbstractObjectType $type */
    $type = $info->getField()->getArgument('input')->getType();
    /** @var \Drupal\graphql\GraphQL\Type\InputObjectType $inputType */
    $inputType = $type->getNamedType();
    $input = $this->extractEntityInput($inputArgs, $inputType, $info);

    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $storage->create($input + [
        $bundleKey => $bundleName,
      ]);

//    if (!$entity->access('create')) {
//      return new EntityCrudOutputWrapper(NULL, NULL, [
//        $this->t('You do not have the necessary permissions to create entities of this type.'),
//      ]);
//    }

    if (($violations = $entity->validate()) && $violations->count()) {
      return new EntityCrudOutputWrapper(NULL, $violations);
    }

    if (($status = $entity->save()) && $status === SAVED_NEW) {
      return new EntityCrudOutputWrapper($entity);
    }

    return NULL;
  }

//  public function resolve($value, array $args, ResolveInfo $info) {
//    return $args['input']['file'];
//  }

}