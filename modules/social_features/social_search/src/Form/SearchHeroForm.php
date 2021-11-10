<?php

namespace Drupal\social_search\Form;

use Drupal\Component\Utility\Xss;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\UrlHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Search Hero Form.
 *
 * @package Drupal\social_search\Form
 */
class SearchHeroForm extends FormBase implements ContainerInjectionInterface {

  /**
   * The request.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * SearchHeroForm constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The route match.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   */
  public function __construct(RequestStack $requestStack) {
    $this->requestStack = $requestStack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'search_hero_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $form['search_input'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search'),
      '#title_display' => 'invisible',
    ];

    // Pre-fill search input on the search group page.
    $form['search_input']['#default_value'] = $this->routeMatch
      ->getParameter('keys');

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
    ];
    $form['#cache']['contexts'][] = 'url';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $current_route = $this->routeMatch->getRouteName();
    $route_parts = explode('.', $current_route);
    if (empty($form_state->getValue('search_input'))) {
      // Redirect to the search page with empty search values.
      $new_route = "view.{$route_parts[1]}.page_no_value";
      $search_group_page = Url::fromRoute($new_route);
    }
    else {
      // Redirect to the search page with filters in the GET parameters.
      $search_input = Xss::filter($form_state->getValue('search_input'));
      $search_input = preg_replace('/[\/]+/', ' ', $search_input);
      $search_input = str_replace('&amp;', '&', $search_input);
      $new_route = "view.{$route_parts[1]}.page";
      $search_group_page = Url::fromRoute($new_route, ['keys' => $search_input]);
    }
    $redirect_path = $search_group_page->toString();

    $query = UrlHelper::filterQueryParameters($this->requestStack->getCurrentRequest()->query->all(), ['page']);

    $redirect = Url::fromUserInput($redirect_path, ['query' => $query]);

    $form_state->setRedirectUrl($redirect);
  }

}
