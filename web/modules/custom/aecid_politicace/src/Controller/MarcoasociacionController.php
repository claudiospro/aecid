<?php

namespace Drupal\aecid_politicace\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class MarcoasociacionController.
 */
class MarcoasociacionController extends ControllerBase {

  /*Devuelve el listado de los planes lectores*/
  public function listadoMarcoAsociacion() {
    $data = $this->getDataTableMarcoAsociacion();
    $data['user_role'] = $this->getUserRole();

    return [
      '#theme' => 'aecid_marcoasociacion',
      '#data' => $data,
      '#attached' => array(
        'library' =>  array(
          'aecid_politicace/aecid_politicace.marcoasociacion'
        ),
      ),
    ];
  }

  /*Devuelve vocabulario especifico con sus hijos - AJAX*/
  public function getVocabularyMarcoAsociacion($vid) {
    $logged_in = \Drupal::currentUser()->isAuthenticated();
    if($logged_in && $this->getUserRole() == 'administrator'){
      $is_ajax = \Drupal::request()->isXmlHttpRequest();
      if($is_ajax){
        $data = [];
        $vocabularies = $this->getMarcoAsociacionVocabulariesVids();
        foreach ($vocabularies as $vocabulary) {
          if($vocabulary['vid'] == $vid){
            $data['vid'] = $vocabulary['vid'];
            $data['title'] = $vocabulary['title'];
            $data['start_date'] = date("Y", strtotime($vocabulary['start_date']));
            $data['end_date'] = date("Y", strtotime($vocabulary['end_date']));
            $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vocabulary['vid']);
            if(count($terms) > 0){
              foreach ($terms as $key => $value) {
                $data['items'][] = ['tid' => $value->tid, 'name' => $value->name];
              }
            }
          }
        }
        $content[] = array(
          '#theme' => 'aecid_marcoasociacion_popup',
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

  /*Devuelve todos los vocabularios de tipo marco asociacion a EXCEL O PDF - AJAX*/
  public function getAllVocabulariesMarcoAsociacionExport() {
    $logged_in = \Drupal::currentUser()->isAuthenticated();
    if($logged_in && $this->getUserRole() == 'administrator'){
      $is_ajax = \Drupal::request()->isXmlHttpRequest();
      if($is_ajax){
        $data = [];
        $marcoasociaciones = $this->getMarcoAsociacionVocabulariesVids();
        foreach ($marcoasociaciones as $marcoasociacion) {
          $data[$marcoasociacion['vid']]['title'] = $marcoasociacion['title'];
          $data[$marcoasociacion['vid']]['vid'] = $marcoasociacion['vid'];
          $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($marcoasociacion['vid']);
          if(count($terms) > 0){
            foreach ($terms as $key => $value) {
              $data['items'][] = ['pdtitle' => $marcoasociacion['title'], 'tid' => $value->tid, 'name' => $value->name, 'monto' => '0.00', 'cantinter' => count($this->getIntervencionByResultadoDesarrollo($value->tid))];
            }
          }
        }
        $build[] = array(
          '#theme' => 'aecid_marcoasociacion_export_table',
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

  /*Devuelve vocabulario especifico con sus hijos para exportar a EXCEL O PDF - AJAX*/
  public function getVocabularyMarcoAsociacionExport($vid) {
    $logged_in = \Drupal::currentUser()->isAuthenticated();
    if($logged_in && $this->getUserRole() == 'administrator'){
      $is_ajax = \Drupal::request()->isXmlHttpRequest();
      if($is_ajax){
        $data = [];
        $vocabularies = $this->getMarcoAsociacionVocabulariesVids();
        foreach ($vocabularies as $vocabulary) {
          if($vocabulary['vid'] == $vid){
            $data['vid'] = $vocabulary['vid'];
            $data['title'] = $vocabulary['title'];
            $data['start_date'] = date("Y", strtotime($vocabulary['start_date']));
            $data['end_date'] = date("Y", strtotime($vocabulary['end_date']));
            $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vocabulary['vid']);
            if(count($terms) > 0){
              foreach ($terms as $key => $value) {
                $data['items'][] = ['pdtitle' => $vocabulary['title'], 'tid' => $value->tid, 'name' => $value->name, 'monto' => '0.00', 'cantinter' => count($this->getIntervencionByResultadoDesarrollo($value->tid))];
              }
            }
          }
        }
        $build[] = array(
          '#theme' => 'aecid_marcoasociacion_export_table',
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

  /*Devuelve datos para la vista de plan director*/
  private function getDataTableMarcoAsociacion() {
    $data = [];
    $marcoasociaciones = $this->getMarcoAsociacionVocabulariesVids();
    foreach ($marcoasociaciones as $marcoasociacion) {
      $data[$marcoasociacion['vid']]['title'] = $marcoasociacion['title'];
      $data[$marcoasociacion['vid']]['vid'] = $marcoasociacion['vid'];
      $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($marcoasociacion['vid']);
      if(count($terms) > 0){
        foreach ($terms as $key => $value) {
          $data[$marcoasociacion['vid']]['items'][] = ['tid' => $value->tid, 'name' => $value->name, 'monto' => '0.00', 'cantinter' => count($this->getIntervencionByResultadoDesarrollo($value->tid))];
        }
      }
    }
    return $data;
  }

  /*Devuelve la cantidad de Intervenciones por Resultados de Desarrollo*/
  private function getIntervencionByResultadoDesarrollo($tid) {
    $data = [];
    $nids = \Drupal::entityQuery('node')
                    ->condition('type','intervencion')
                    ->sort('title','ASC')
                    ->execute();

    $nodes =  \Drupal\node\Entity\Node::loadMultiple($nids);
    foreach ($nodes as $node) {
      if(count($node->field_resultados_de_desarrollo) > 0){
        foreach ($node->field_resultados_de_desarrollo as $key => $value) {
          $result = db_query('SELECT * FROM paragraph__field_resultados_de_desarrollo WHERE entity_id = :entity_id', array(':entity_id' => $value->target_id));
          if($result){
            while ($row = $result->fetchAssoc()) {
              if($row['field_resultados_de_desarrollo_target_id'] == $tid){
                $data[$node->nid->value] = $node->title->value;
              }
            }
          }
        }
      }
    }

    return $data;
  }

  /*Devuelve los vocabularios de tipo - Plan Director*/
  private function getMarcoAsociacionVocabulariesVids() {
    $data = [];
    $vocabularies = \Drupal\taxonomy\Entity\Vocabulary::loadMultiple();
    foreach($vocabularies as $vid => $vocabulary){
      $config_name = 'taxonomy.vocabulary.'.$vid;
      $config_result = \Drupal::config($config_name);
      if($config_result->get('third_party_settings')['taxonomy_vocabulary_date']['type'] == 'Marco de Asociación'){
        $data[] = [
                  'vid' => $vid,
                  'title' => $config_result->get('name'),
                  'type' => $config_result->get('third_party_settings')['taxonomy_vocabulary_date']['type'],
                  'start_date' => $config_result->get('third_party_settings')['taxonomy_vocabulary_date']['start_date'],
                  'end_date' => $config_result->get('third_party_settings')['taxonomy_vocabulary_date']['end_date']
                ];
      }
    }

    return $data;
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
