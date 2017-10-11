<?php

namespace Drupal\aecid_politicace\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class PlandirectorController.
 */
class PlandirectorController extends ControllerBase {

  /*Devuelve el listado de los planes lectores*/
  public function listadoPlanDirector() {
    $data = $this->getDataTablePlanDirector();
    $data['user_role'] = $this->getUserRole();

    return [
      '#theme' => 'aecid_plandirector',
      '#data' => $data,
      '#attached' => array(
        'library' =>  array(
          'aecid_politicace/aecid_politicace.plan_director'
        ),
      ),
    ];
  }

  /*Devuelve vocabulario especifico con sus hijos - AJAX*/
  public function getVocabularyPlanDirector($vid) {
    $logged_in = \Drupal::currentUser()->isAuthenticated();
    if($logged_in && $this->getUserRole() == 'administrator'){
      $is_ajax = \Drupal::request()->isXmlHttpRequest();
      if($is_ajax){
        $data = [];
        $vocabularies = $this->getPlanDirectorVocabulariesVids();
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
          '#theme' => 'aecid_plandirector_popup',
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

  /*Devuelve todos los vocabularios de tipo plan director a EXCEL O PDF - AJAX*/
  public function getAllVocabulariesPlanDirectorExport() {
    $logged_in = \Drupal::currentUser()->isAuthenticated();
    if($logged_in && $this->getUserRole() == 'administrator'){
      $is_ajax = \Drupal::request()->isXmlHttpRequest();
      if($is_ajax){
        $data = [];
        $plandirectores = $this->getPlanDirectorVocabulariesVids();
        foreach ($plandirectores as $plandirector) {
          $data[$plandirector['vid']]['title'] = $plandirector['title'];
          $data[$plandirector['vid']]['vid'] = $plandirector['vid'];
          $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($plandirector['vid']);
          if(count($terms) > 0){
            foreach ($terms as $key => $value) {
              $data['items'][] = [
                  'pdtitle' => $plandirector['title'],
                  'tid' => $value->tid,
                  'name' => $value->name,
                  'monto' => '2.00',
                  'cantinter' => count($this->getIntervencionByPrioridadSectorial($value->tid))
                  ];
            }
          }
        }
        $build[] = array(
          '#theme' => 'aecid_plandirector_export_table',
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
  public function getVocabularyPlanDirectorExport($vid) {
    $logged_in = \Drupal::currentUser()->isAuthenticated();
    if($logged_in && $this->getUserRole() == 'administrator'){
      $is_ajax = \Drupal::request()->isXmlHttpRequest();
      if($is_ajax){
        $data = [];
        $vocabularies = $this->getPlanDirectorVocabulariesVids();
        foreach ($vocabularies as $vocabulary) {
          if($vocabulary['vid'] == $vid){
            $data['vid'] = $vocabulary['vid'];
            $data['title'] = $vocabulary['title'];
            $data['start_date'] = date("Y", strtotime($vocabulary['start_date']));
            $data['end_date'] = date("Y", strtotime($vocabulary['end_date']));
            $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vocabulary['vid']);
            if(count($terms) > 0){
              foreach ($terms as $key => $value) {
                $data['items'][] = ['pdtitle' => $vocabulary['title'], 'tid' => $value->tid, 'name' => $value->name, 'monto' => '0.00', 'cantinter' => count($this->getIntervencionByPrioridadSectorial($value->tid))];
              }
            }
          }
        }
        $build[] = array(
          '#theme' => 'aecid_plandirector_export_table',
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
  private function getDataTablePlanDirector() {
    $data = [];
    $plandirectores = $this->getPlanDirectorVocabulariesVids();
    foreach ($plandirectores as $plandirector) {
      $data[$plandirector['vid']]['title'] = $plandirector['title'];
      $data[$plandirector['vid']]['vid'] = $plandirector['vid'];
      $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($plandirector['vid']);
      if(count($terms) > 0){
        foreach ($terms as $key => $value) {
          $data[$plandirector['vid']]['items'][] = ['tid' => $value->tid, 'name' => $value->name,
              'monto' => $this->getIntervencionMontos($value->tid), 'cantinter' => count($this->getIntervencionByPrioridadSectorial($value->tid))
              // ,'test' => $this->getIntervencionMontos($value->tid)
              ];
        }
      }
    }
    return $data;
  }
  
  /*retorna el monto total*/
  private function getIntervencionMontos($tid) {
      $monto = 0;
      $data = $this->getIntervencionMontosData($tid);
      
      foreach ($data['intervencion'] as $row) {
          $monto = 0;
          $submonto = 0;
          foreach($row['financiadores'] as $f) {
              $cambio = $this->getIntervencionMontosTipoCambio($data['tipocambio'][$f['moneda']], $f['moneda'], $row['fecha_inicio']);
         
              $submonto += $f['monto'] * $cambio;                 
          }
          $monto += $submonto;
      }
      $cambio_soles = $this->getIntervencionMontosTipoCambio($data['tipocambio']['sp'], 'sp', $row['fecha_inicio']);
      $cambio_dolares = $this->getIntervencionMontosTipoCambio($data['tipocambio']['da'], 'da', $row['fecha_inicio']);
      
      $monto += $row['aport_local_soles'] * $cambio_soles;
      $monto += $row['aport_local_dolares'] * $cambio_dolares;
      $monto += $row['aport_local_ueros'];
       
      return $monto;
  }
  
  /* optiene y ordena toda data */
  private function getIntervencionMontosData($tid) {
    $data = [];
    $moneda = ['$'=> 'da', '€'=> 'eu', 'S/'=> 'sp', 'Nuevos Soles' => 'sp', 'Soles' => 'sp', 'Dólares americanos' => 'da'];
    $nids = \Drupal::entityQuery('node')
                    ->condition('type','tipo_de_cambio')
                    ->execute();

    $nodes =  \Drupal\node\Entity\Node::loadMultiple($nids);
    foreach ($nodes as $node) {
                $data['tipocambio'][$moneda[$node->field_moneda_tipo_cambio->value]][] = [ 'fecha' => $node->field_fecha_tipo_cambio->value, 'valor' => $node->field_tasa_tipo_cambio->value ];
    }

    $nids = \Drupal::entityQuery('node')
                    ->condition('type','intervencion')
                    ->sort('title','ASC')
                    ->execute();

    $nodes =  \Drupal\node\Entity\Node::loadMultiple($nids);
    foreach ($nodes as $node) {
      if(count($node->field_orientaciones_generales) > 0){
        foreach ($node->field_orientaciones_generales as $key => $value) {
          $result = db_query('SELECT * FROM paragraph__field_orientaciones_generales WHERE entity_id = :entity_id', array(':entity_id' => $value->target_id));
          if($result){
            while ($row = $result->fetchAssoc()) {
              if($row['field_orientaciones_generales_target_id'] == $tid){


                $financiadores = [];
                if ($node->field_financiadores) {
                    $paragraph = $node->field_financiadores->getValue();
                    foreach ( $paragraph as $element ) {
                      $p = \Drupal\paragraphs\Entity\Paragraph::load( $element['target_id'] );
                      $financiadores [] = [
                        'moneda' => $moneda[$p->field_moneda->value],
                        'monto' =>   $p->field_monto->value
                      ];
                    }
                }
                // $data['intervencion'][$node->nid->value] =$financiadores;
                $data['intervencion'][$node->nid->value] =[
                    'fecha_inicio' => $node->field_fecha_inicio->value,
                    'aport_local_soles' => $node->field_aport_local_soles->value,
                    'aport_local_dolares' => $node->field_aport_local_dolares->value,
                    'aport_local_ueros' => $node->field_aport_local_ueros->value,
                    
                    'financiadores' => $financiadores
                ] ;
              }
            }
          }
        }
      }
    }

    return $data;    
  }
  
  /*optiene el tipo de cambio*/
  private function getIntervencionMontosTipoCambio($data, $moneda, $fechaInicio) {
      $cambio = 1;
      if ($moneda != 'eu') {
          $proximo = $data[0]['fecha'];

          foreach ($data as $m) {
              $proximo = $this->getIntervencionMontosFechaCercana($fechaInicio, $proximo, $m['fecha']);
          }

          foreach ($data as $m) {
              if ($proximo == $m['fecha']) {
                  $cambio = $m['valor'];
              }
          }    
      }
      return $cambio;
  }

  /* compara que fecha es mas cernada a una fecha */
  private function getIntervencionMontosFechaCercana($in_fecha, $in_proximo, $in_comparacion) {
	$caso= 0; 
	$fecha = new \DateTime($in_fecha);
	$proximo = new \DateTime($in_proximo);
	$comparacion = new \DateTime($in_comparacion);

	if (date_format($fecha, 'Y-n') == date_format($proximo, 'Y-n') && date_format($fecha, 'Y-n') != date_format($comparacion, 'Y-n'))
	{
		return $in_proximo;
	}
	if (date_format($fecha, 'Y-n') != date_format($proximo, 'Y-n') && date_format($fecha, 'Y-n') == date_format($comparacion, 'Y-n'))
	{
		return $in_comparacion;
	}

	$c1 = $fecha->diff($proximo)->format('%a');
	$c2 = $fecha->diff($comparacion)->format('%a');

	if ($c1 < $c2) 
		return $in_proximo;
	elseif ($c1 > $c2)
		return $in_comparacion;
	elseif ($c1 == $c2)
		if (strtotime($in_proximo) >= strtotime($in_comparacion) )
			return $in_proximo;
		else
			return $in_comparacion;      
  }
  
  
  /*Devuelve la cantidad de Intervenciones por Prioridad Sectorial*/
  private function getIntervencionByPrioridadSectorial($tid) {
    $data = [];
    $nids = \Drupal::entityQuery('node')
                    ->condition('type','intervencion')
                    ->sort('title','ASC')
                    ->execute();

    $nodes =  \Drupal\node\Entity\Node::loadMultiple($nids);
    foreach ($nodes as $node) {
      if(count($node->field_orientaciones_generales) > 0){
        foreach ($node->field_orientaciones_generales as $key => $value) {
          $result = db_query('SELECT * FROM paragraph__field_orientaciones_generales WHERE entity_id = :entity_id', array(':entity_id' => $value->target_id));
          if($result){
            while ($row = $result->fetchAssoc()) {
              if($row['field_orientaciones_generales_target_id'] == $tid){
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
  private function getPlanDirectorVocabulariesVids() {
    $data = [];
    $vocabularies = \Drupal\taxonomy\Entity\Vocabulary::loadMultiple();
    foreach($vocabularies as $vid => $vocabulary){
      $config_name = 'taxonomy.vocabulary.'.$vid;
      $config_result = \Drupal::config($config_name);
      if($config_result->get('third_party_settings')['taxonomy_vocabulary_date']['type'] == 'Plan Director'){
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
