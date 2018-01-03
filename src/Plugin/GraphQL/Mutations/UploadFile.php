<?php

namespace Drupal\graphql_file_upload\Plugin\GraphQL\Mutations;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\graphql\Annotation\GraphQLMutation;
use Drupal\graphql\GraphQL\Type\InputObjectType;
use Drupal\graphql_core\Plugin\GraphQL\Mutations\Entity\CreateEntityBase;
use Drupal\graphql_file_upload\UploadException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Core\Session\AccountProxyInterface;

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
 *   type = "String",
 *   arguments = {
 *     "input" = "FileInput"
 *   }
 * )
 */
class UploadFile extends CreateEntityBase {


  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The Upload Handler.
   *
   * @var \Drupal\graphql_file_upload\UploadHandlerInterface
   */
  protected $uploadHandler;

  /**
   * The Upload Save.
   *
   * @var \Drupal\graphql_file_upload\GraphQLUploadSaveInterface
   */
  protected $uploadSave;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   *   The HTTP request object.
   */
  protected $request;

  /**
   * Constructs a Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param mixed $pluginDefinition
   *   The plugin implementation definition.
   * @param EntityTypeManagerInterface $entityTypeManager
   *   The plugin implemented entityTypeManager
   * @param \Drupal\graphql_file_upload\UploadHandler $uploadHandler
   *   The upload Handler
   * @param \Drupal\graphql_file_upload\GraphQLUploadSave
   *   The upload save
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, EntityTypeManagerInterface $entityTypeManager, $uploadHandler, $uploadSave) {
    $this->entityTypeManager = $entityTypeManager;
    $this->uploadHandler = $uploadHandler;
    $this->uploadSave = $uploadSave;
    $this->currentUser = \Drupal::currentUser();
    parent::__construct($configuration, $pluginId, $pluginDefinition, $entityTypeManager);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('entity_type.manager'),
      $container->get('graphql_file_upload.upload_handler'),
      $container->get('graphql_file_upload.upload_save')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function extractEntityInput(array $inputArgs, InputObjectType $inputType, ResolveInfo $info) {
    return [
      'filename' => $inputArgs['filename']
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {


    foreach ($_FILES as $file) {
      // The upload handler appended the txt extension to the file for
      // security reasons. We will remove it in this callback.
      $old_filepath = $file['tmp_name'][0];

      // Potentially we moved the file already, so let's check first whether
      // we still have to move.
      if (file_exists($old_filepath)) {
        // Finaly rename the file and add it to results.
        $new_filepath =  'public://' . $file['name'];
        $move_result = file_unmanaged_move($file['tmp_name'][0]);

        if ($move_result) {
          $return['uploaded_files'][] = [
            'path' => $move_result,
            'filename' => $file['name'],
          ];
        }
        else {
          drupal_set_message(self::t('There was a problem while processing the file named @name', ['@name' => $file['name']]), 'error');
        }
      }
    }

    $additional_validators = ['file_validate_size' => '2M'];

    // We do some casting because $form_state->getValue() might return NULL.
    foreach ($return['uploaded_files'] as $file) {
      if (file_exists($file['path'])) {
        $entity = $this->uploadSave->createFile(
          $file['path'],
          $this->getUploadLocation(),
          'jpg jpeg gif png txt doc xls pdf ppt pps odt ods odp',
          $this->currentUser,
          $additional_validators
        );
        $files[] = $entity;
      }
    }

    $entity = $files[0];

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