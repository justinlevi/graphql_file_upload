<?php

namespace Drupal\custom_graphql_file_upload\Plugin\GraphQL\Scalars;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\GraphQL\Scalars\GraphQLString;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @GraphQLScalar(
 *   id = "file_upload",
 *   name = "FileUpload",
 *   data_type = "id",
 *   description = @Translation("Name of the key holding the uploaded file data in the $_FILES variable.")
 * )
 */
class FileUpload extends GraphQLString implements ContainerFactoryPluginInterface {

  /**
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, RequestStack $request_stack) {
    $this->requestStack = $request_stack;
    $this->configuration = $configuration;
    $this->pluginId = $pluginId;
    $this->pluginDefinition = $pluginDefinition;
//    $this->constructPlugin($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @todo remove when https://github.com/drupal-graphql/graphql/issues/420
   */
  public function getName() {
    return $this->getPluginDefinition()['name'];
  }

  /**
   * {@inheritdoc}
   *
   * @todo remove when https://github.com/drupal-graphql/graphql/issues/420
   */
  public function getDescription() {
    return $this->getPluginDefinition()['description'];
  }

  /**
   * Helper method to identify the file by its name.
   *
   * @param string $file_name
   *   The name of the file to look for.
   *
   * @return string|null
   *   The array key in the file bag.
   */
  protected function findFileIdentifier(string $file_name): ?string {
    /** @var \Symfony\Component\HttpFoundation\File\UploadedFile[] $all_files */
    $all_files = $this->requestStack->getCurrentRequest()->files->all();
    foreach ($all_files AS $identifier => $file) {
      if ($file->getClientOriginalName() === $file_name) {
        return $identifier;
      }
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function isValidValue($value) {
    return is_string($value) && strlen($value) !== 0 && $this->findFileIdentifier($value) !== NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function parseValue($value) {
    // Drupal expects files to reside under 'files' key
    // so we have to manually move the file to expected location.
    // @see file_save_upload()
    // @see \Drupal\file\Element\ManagedFile::processManagedFile()
    $files_bag = $this->requestStack->getCurrentRequest()->files;
    $all_files = $files_bag->get('files', []);
    $identifier = $this->findFileIdentifier($value);
    $file = $files_bag->get($identifier);

    // Prevent key collision.
    if (isset($all_files[$identifier])) {
      $identifier = uniqid($identifier);
    }

    // Move the file.
    $all_files[$identifier] = $file;
    $files_bag->set('files', $all_files);

    // Save the uploaded file as managed file entity.
    $file = file_save_upload($identifier, $this->getFileUploadValidators(), $this->getFileUploadDirectory(), 0);

    // Return the file entity id.
    return parent::parseValue($file ? $file->id() : NULL);
  }

  /**
   * {@inheritdoc}
   */
  public function getValidationError($value = null) {
    return is_string($value) ? sprintf('File "%s" not found in request.', $value) : parent::getValidationError($value);
  }

  /**
   * Helper method that will return file validators.
   *
   * @return array
   */
  public function getFileUploadValidators() {
    return [];
  }

  /**
   * Helper method that will return the target directory.
   *
   * @return string|false
   */
  public function getFileUploadDirectory() {
    return FALSE;
  }

}
