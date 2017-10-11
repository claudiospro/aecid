<?php

namespace Drupal\aecid_utilidades\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\aecid_utilidades\Controller\CodigocadProcessDataController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class CodigocadController.
 */
class CodigocadController extends ControllerBase {

  public function getCodigoCadByIdForExport($tid) {
    $logged_in = \Drupal::currentUser()->isAuthenticated();
    if($logged_in && $this->getUserRole() == 'administrator'){
      $is_ajax = \Drupal::request()->isXmlHttpRequest();
      if($is_ajax){
        $data = CodigocadProcessDataController::getCodigoCadByIdForExport($tid);
        $build[] = array(
          '#theme' => 'aecid_codigos_cad_table_export',
          '#data' => $data,
        );
        return new JsonResponse([render($build)]);
      }else{
        return new RedirectResponse(\Drupal::url('<front>'));
      }
    }else{
      return new RedirectResponse(\Drupal::url('<front>'));
    }
  }

  public function getAllCodigoCadForExport() {
    $logged_in = \Drupal::currentUser()->isAuthenticated();
    if($logged_in && $this->getUserRole() == 'administrator'){
      $is_ajax = \Drupal::request()->isXmlHttpRequest();
      if($is_ajax){
        $data = CodigocadProcessDataController::getAllCodigoCadForExport();
        $build[] = array(
          '#theme' => 'aecid_codigos_cad_table_export',
          '#data' => $data,
        );
        return new JsonResponse([render($build)]);
      }else{
        return new RedirectResponse(\Drupal::url('<front>'));
      }
    }else{
      return new RedirectResponse(\Drupal::url('<front>'));
    }
  }

  /**
   * Portada de Estadísticas.
   *
   * @return string
   */
  public function getPortadaEstadisticas() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Contenido de Portadas de Estadísticas')
    ];
  }

  /*
  ======================================================================
  = Devuelve listado completo de Códigos CAD y sus hijos hasta 2 niveles
  ======================================================================
  */
  public function getCodigosCadLista() {
    $data = CodigocadProcessDataController::getCodigosCad();
    $data['user_role'] = $this->getUserRole();
    return [
      '#theme' => 'aecid_codigos_cad_lista',
      '#data' => $data,
      '#attached' => array(
        'library' =>  array(
          'aecid_utilidades/aecid_utilidades.codigo_cad'
        ),
      ),
    ];
  }

  /*
  =====================================================================
  = Devuelve Código CAD por su (TID) junto con sus hijos si los tuviera
  =====================================================================
  */
  public function getCodigoCadById($tid) {
    $logged_in = \Drupal::currentUser()->isAuthenticated();
    if($logged_in){
      $is_ajax = \Drupal::request()->isXmlHttpRequest();
      if($is_ajax){
        return new JsonResponse(CodigocadProcessDataController::getCodigoCadById($tid));
      }else{
        return new RedirectResponse(\Drupal::url('<front>'));
      }
    }else{
      return new RedirectResponse(\Drupal::url('<front>'));
    }
  }

  /*
  ====================================================================
  = Elimina Código CAD por su (TID) junto con sus hijos si los tuviera
  ====================================================================
  */
  public function checkDeleteCodigoCadById($tid) {
    $logged_in = \Drupal::currentUser()->isAuthenticated();
    if($logged_in){
      $is_ajax = \Drupal::request()->isXmlHttpRequest();
      if($is_ajax){
        return new JsonResponse(CodigocadProcessDataController::checkDeleteCodigoCadById($tid));
      }else{
        return new RedirectResponse(\Drupal::url('<front>'));
      }
    }else{
      return new RedirectResponse(\Drupal::url('<front>'));
    }
  }
  public function deleteCodigoCadById($tid) {
    $logged_in = \Drupal::currentUser()->isAuthenticated();
    if($logged_in){
      $is_ajax = \Drupal::request()->isXmlHttpRequest();
      if($is_ajax){
        return new JsonResponse(CodigocadProcessDataController::deleteCodigoCadById($tid));
      }else{
        return new RedirectResponse(\Drupal::url('<front>'));
      }
    }else{
      return new RedirectResponse(\Drupal::url('<front>'));
    }
  }

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
