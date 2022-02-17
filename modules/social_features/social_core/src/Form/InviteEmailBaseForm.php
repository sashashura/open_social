<?php

namespace Drupal\social_core\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class InviteBaseForm.
 */
class InviteEmailBaseForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Constructs a new BulkGroupInvitation Form.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(
    RouteMatchInterface $route_match,
    LoggerChannelFactoryInterface $logger_factory,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->routeMatch = $route_match;
    $this->loggerFactory = $logger_factory;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match'),
      $container->get('logger.factory'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'invite_email_base_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['users_fieldset'] = [
      '#type' => 'fieldset',
      '#tree' => TRUE,
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
      '#attributes' => [
        'class' => [
          'form-horizontal',
        ],
      ],
    ];

    // @todo Validation should go on the element and return a nice list.
    $form['users_fieldset']['user'] = [
      '#title' => $this->t('Find people by name or email address'),
      '#type' => 'select2',
      '#description' => $this->t('You can enter or paste multiple entries separated by comma or semicolon'),
      '#multiple' => TRUE,
      '#tags' => TRUE,
      '#autocomplete' => TRUE,
      '#selection_handler' => 'social',
      '#target_type' => 'user',
      '#select2' => [
        'tags' => TRUE,
        'placeholder' => t('Jane Doe, johndoe@example.com'),
        'tokenSeparators' => [',', ';'],
        'autocomplete' => FALSE,
      ],
      '#required' => TRUE,
      '#validated' => TRUE,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send your invite(s) by email'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * Custom function to extract email addresses from a string.
   */
  public function extractEmailsFrom($string) {
    // Remove select2 ID parameter.
    $string = str_replace('$ID:', '', $string);
    preg_match_all("/[\._a-zA-Z0-9+-]+@[\._a-zA-Z0-9+-]+/i", $string, $matches);
    return $matches[0];
  }

}
