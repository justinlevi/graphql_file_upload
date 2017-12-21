<?php

namespace Drupal\store_graph_upload\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;

/**
 * Derives EntityFileFieldUpload scalar for each file field instance.
 */
class EntityFileFieldUploadDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $fieldManager;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $bundleInfo;

  /**
   * @var \Drupal\Core\Field\FieldTypePluginManagerInterface
   */
  protected $fieldTypeManager;

  /**
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * EntityFileFieldUploadDeriver constructor.
   *
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $field_manager
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $bundle_info
   * @param \Drupal\Core\Field\FieldTypePluginManagerInterface $field_type_manager
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   */
  public function __construct(
    EntityFieldManagerInterface $field_manager,
    EntityTypeManagerInterface $entity_manager,
    EntityTypeBundleInfoInterface $bundle_info,
    FieldTypePluginManagerInterface $field_type_manager,
    ThemeManagerInterface $theme_manager
  ) {
    $this->fieldManager = $field_manager;
    $this->entityTypeManager = $entity_manager;
    $this->bundleInfo = $bundle_info;
    $this->fieldTypeManager = $field_type_manager;
    $this->themeManager = $theme_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $basePluginId) {
    return new static(
      $container->get('entity_field.manager'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('plugin.manager.field.field_type'),
      $container->get('theme.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    $this->derivatives = [];

    foreach (store_graph_exposed_content_entities() AS $entity_type_id) {
      $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
      $bundle_entity_type_id = $entity_type->getBundleEntityType();
      $bundles = empty($bundle_entity_type_id) ? [$entity_type_id] : array_keys($this->bundleInfo->getBundleInfo($entity_type_id));

      foreach ($bundles AS $bundle) {
        $fields = $this->fieldManager->getFieldDefinitions($entity_type_id, $bundle);
        foreach ($fields AS $field) {
          if (in_array($field->getType(), ['file', 'image'])) {
            $key = $entity_type_id . ':' . $bundle . ':' . $field->getName();
            if ($field->getFieldStorageDefinition()->isBaseField()) {
              $key = $entity_type_id . ':' . $field->getName();
              $bundle = $entity_type_id;
            }
            if (!isset($this->derivatives[$key])) {
              $this->derivatives[$key] = $this->buildDefinition($entity_type_id, $bundle, $field) + $basePluginDefinition;
            }
          }
        }
      }
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

  /**
   * Helper method to create definition of derivative from provided values.
   *
   * @param string $entity_type_id
   * @param string $bundle
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field
   *
   * @return array
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function buildDefinition(string $entity_type_id, string $bundle, FieldDefinitionInterface $field): array {
    /** @var \Drupal\file\Plugin\Field\FieldType\FileItem $item */
    $item = $this->fieldTypeManager->createInstance(
      $field->getType(),
      [
        'field_definition' => $field,
        'name' => $field->getName(),
        'parent' => NULL
      ]
    );

    $description = $this->themeManager->render('file_upload_help', [
      'description' => store_graph_untranslated_string($field->getDescription()),
      'upload_validators' => $item->getUploadValidators(),
      'cardinality' => $field->getFieldStorageDefinition()->getCardinality()
    ]);

    return [
      'name' => store_graph_upload_get_file_field_upload_input_name($entity_type_id, $bundle, $field->getName()),
      'description' => store_graph_parse_description($description),
      'file_upload_validators' => $item->getUploadValidators(),
      'file_upload_destination' => $item->getUploadLocation()
    ];
  }

}
