<?php

namespace Drupal\aecid_actores\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\aecid_actores\Controller\ActorProcessDataController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ActorController.
 */
class ActorController extends ControllerBase {

  /*============== CONTROLADORES PARA ACTORES ESPAÑOLES ===============*/
  /**
   * Lista de Actores Españoles.
   *
   * @return string
   */
  public function getActorEspanolLista() {
    $data = ActorProcessDataController::getDataByActorType('espanol');
    return [
      '#theme' => 'aecid_actor_espanol_lista',
      '#data' => $data,
      '#attached' => array(
        'library' =>  array(
          'aecid_actores/aecid_actores.global'
        ),
      ),
    ];
  }

  public function getActorEspanolByNid($nid) {
    $logged_in = \Drupal::currentUser()->isAuthenticated();
    if($logged_in && $this->getUserRole() == 'administrator'){
      $is_ajax = \Drupal::request()->isXmlHttpRequest();
      if($is_ajax){
        $data['nid'] = $nid;
        $content[] = array(
          '#theme' => 'aecid_actor_popup',
          '#data' => $data,
        );
        $build[] = array(
          '#theme' => 'aecid_lightbox',
          '#content' => render($content),
        );
        return new JsonResponse(['data' => render($build)]);
      }else{
        return new RedirectResponse(\Drupal::url('<front>'));
      }
    }else{
      return new RedirectResponse(\Drupal::url('<front>'));
    }
  }

  public function setActorShowStateEspanolByNid($nid, $state) {
    $logged_in = \Drupal::currentUser()->isAuthenticated();
    if($logged_in && $this->getUserRole() == 'administrator'){
      $is_ajax = \Drupal::request()->isXmlHttpRequest();
      if($is_ajax){
        $node = \Drupal\node\Entity\Node::load($nid);
        $node->set('field_mostrar', $state);
        $node->save();
        return new JsonResponse([]);
      }else{
        return new RedirectResponse(\Drupal::url('<front>'));
      }
    }else{
      return new RedirectResponse(\Drupal::url('<front>'));
    }
  }

  /*============== CONTROLADORES PARA ACTORES INTERNACIONALES ===============*/
  /**
   * Lista de Actores Internacionales.
   *
   * @return string
   */
  public function getActorInternacionalLista() {
    $data = ActorProcessDataController::getDataByActorType('internacional');
    return [
      '#theme' => 'aecid_actor_internacional_lista',
      '#data' => $data,
      '#attached' => array(
        'library' =>  array(
          'aecid_actores/aecid_actores.global'
        ),
      ),
    ];
  }

  /*============== CONTROLADORES PARA ACTORES PERUANOS ===============*/
  /**
   * Lista de Actores Peruanos.
   *
   * @return string
   */
  public function getActorPeruanoLista() {
    $data = ActorProcessDataController::getDataByActorType('peruano');
    return [
      '#theme' => 'aecid_actor_peruano_lista',
      '#data' => $data,
      '#attached' => array(
        'library' =>  array(
          'aecid_actores/aecid_actores.global'
        ),
      ),
    ];
  }

  /*========= Tools =========*/
  private function getUserRole() {
    $user_role = '';
    $current_user = \Drupal::currentUser();
    $roles = $current_user->getRoles();
    foreach ($roles as $role) {
      if($role == 'administrator'){
        $user_role = 'administrator';
      }elseif($role == 'actor') {
        $user_role = 'actor';
      }
    }

    return $user_role;
  }
}
