<?php

namespace Drupal\intervenciones\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ListaIntervecionController.
 */
class ListaIntervecionController extends ControllerBase {

  public static function estadoIntervencion($nid) {
    $data = self::checkEstadoIntervencion($nid);
    return $data;
  }

  public static function getInputCheckState($nid) {
    $output = array(
      '#markup' => '<input type="checkbox" name="inter_state[]" value="'.$nid.'">',
      '#allowed_tags' => ['input'],
    );

    return new Response(render($output));
  }

  //Devuelve tabla de intervenciones para exportar - INTERVENCIONES
  public function getInterTableForExport($inters) {
    $logged_in = \Drupal::currentUser()->isAuthenticated();
    if($logged_in){
      $is_ajax = \Drupal::request()->isXmlHttpRequest();
      if($is_ajax){
        $data = [];
        $nids_string = substr($inters, 0, strlen($inters) - 1);
        $nids = explode(',', $nids_string);
        foreach ($nids as $nid) {
          //$datetime = new \DateTime($node->created->value);
          $node = \Drupal\node\Entity\Node::load($nid);
          $year = date("Y", strtotime($node->field_fecha_inicio[0]->value));
          $data[] = ['codigo' => $node->field_codigo_aecid[0]->value, 'ano' => $year, 'titulo' => $node->title->value];
        }
        $html[] = array(
          '#theme' => 'intervenciones_table_simple_export',
          '#data' => $data,
        );
        return new JsonResponse(render($html));
      }else{
        return new RedirectResponse(\Drupal::url('<front>'));
      }
    }else{
      return new RedirectResponse(\Drupal::url('<front>'));
    }
  }

  //Devuelve observaciones -INTERVENCIONES
  public static function observacionIntervencion($nid) {
    $data = '0';
    $result = self::sqlProccess('SELECT * FROM node_revision__field_observaciones_revision WHERE entity_id = :nid', array(':nid' => $nid));
    if ($result) {
      while ($row = $result->fetchAssoc()) {
        $data = $row['field_observaciones_revision_value'];
      }
    }
    return $data;
  }

  //Devuelve el departamento - MAPEO
  public static function departamentoIntervencion($tid) {
    $data = '';
    $result = self::sqlProccess('SELECT * FROM paragraph__field_departamento WHERE entity_id = :pid', array(':pid' => $tid));
    if ($result) {
      while ($row = $result->fetchAssoc()) {
        $tid_id = $row['field_departamento_target_id'];
        $terms = \Drupal::service('entity_type.manager')->getStorage("taxonomy_term")->loadAllParents($tid_id);
        $list = [];
        foreach ($terms as $term) {
          if(self::compareUbigeo($term->tid->value) != ''){
            $data = $term->name->value;
          }
        }
      }
    }
    return $data;
  }
  private static function compareUbigeo($tid) {
    $term = [];
    $ubigeo = self::getTaxonomyTree('ubigeo');
    foreach ($ubigeo as $value) {
      if($value->tid == $tid){
        $term['depth'] = $value->depth;
        $term['name'] = $value->name;
      }
    }

    if($term['depth'] == 0){
      return $term['name'];
    }else{
      return '';
    }
    return '';
  }

  //Devuelve canalizador - MAPEO
  public static function canalizadorMapeo($pid) {
    $data = '';
    $result = self::sqlProccess('SELECT * FROM paragraph__field_canalizadores WHERE entity_id = :pid', array(':pid' => $pid));
    if ($result) {
      while ($row = $result->fetchAssoc()) {
        $profile_id = $row['field_canalizadores_target_id'];
        $result_financiador_1 = self::sqlProccess('SELECT nid, title FROM node_field_data WHERE nid = :nid', array(':nid' => $profile_id));
        if($result_financiador_1){
          while ($fin_1 = $result_financiador_1->fetchAssoc()) {
            $data = $fin_1['title'];
          }
        }

        if(data == ''){
          $result_financiador_2 = self::sqlProccess('SELECT entity_id, field_nombre_largo_value FROM user__field_nombre_largo WHERE entity_id = :entity_id', array(':entity_id' => $profile_id));
          if($result_financiador_2){
            while ($fin_2 = $result_financiador_2->fetchAssoc()) {
              $data = $fin_2['field_nombre_largo_value'];
            }
          }
        }
      }
    }

    return $data;
  }

  //Devuelve canalizador - MAPEO
  public static function financiadorMapeo($pid) {
    $data = '';
    $result = self::sqlProccess('SELECT * FROM paragraph__field_financiadores WHERE entity_id = :pid', array(':pid' => $pid));
    if ($result) {
      while ($row = $result->fetchAssoc()) {
        $profile_id = $row['field_financiadores_target_id'];
        $result_financiador_1 = self::sqlProccess('SELECT nid, title FROM node_field_data WHERE nid = :nid', array(':nid' => $profile_id));
        if($result_financiador_1){
          while ($fin_1 = $result_financiador_1->fetchAssoc()) {
            $data = $fin_1['title'];
          }
        }

        if(data == ''){
          $result_financiador_2 = self::sqlProccess('SELECT entity_id, field_nombre_largo_value FROM user__field_nombre_largo WHERE entity_id = :entity_id', array(':entity_id' => $profile_id));
          if($result_financiador_2){
            while ($fin_2 = $result_financiador_2->fetchAssoc()) {
              $data = $fin_2['field_nombre_largo_value'];
            }
          }
        }
      }
    }

    return $data;
  }

  //Devuelve el mapa interactivo - MAPEO
  public function mapaInteractivo() {
    $data = '';
    $is_ajax = \Drupal::request()->isXmlHttpRequest();
    if($is_ajax){
      $html[] = array(
        '#theme' => 'mapa_intervencion_mapeo',
        '#data' => NULL,
      );
      $data = render($html);
      return new JsonResponse([$data]);
    }else{
      return new RedirectResponse(\Drupal::url('<front>'));
    }
  }

  /*Filtra provincias por departamento*/
  public function mapaInterFilterDpto($tid) {
    $data = [];
    $intervenciones = [];
    $dpto_name = \Drupal\taxonomy\Entity\Term::load($tid)->getName();
    $is_ajax = \Drupal::request()->isXmlHttpRequest();
    if($is_ajax){
      $data['parent_id'] = $tid;
      $provs = self::intervecionesProvinciaPorDepartamento($tid);

      foreach ($provs as $key => $value) {
        if(strtolower($key) != strtolower('Regional')){
          array_push($data, array('tid' => $value['tid'], 'departamento' => $key, 'cantidad' => count($value['intervenciones'])));
          if(count($value['intervenciones']) > 0){
            foreach ($value['intervenciones'] as $k => $v) {
              $node = \Drupal\node\Entity\Node::load($v);
              $intervenciones['intervenciones'][] = ['title' => $node->title->value, 'nid' => $node->nid->value];
            }
          }
        }
      }

      foreach ($provs as $key => $value) {
        if(strtolower($key) == strtolower('Regional')){
          $data['regional']['cantidad'] = count($value['intervenciones']);
          if(count($value['intervenciones']) > 0){
            foreach ($value['intervenciones'] as $k => $v) {
              $node = \Drupal\node\Entity\Node::load($v);
              $intervenciones['intervenciones'][] = ['title' => $node->title->value, 'nid' => $node->nid->value];
            }
          }
        }
      }

      $html[] = array(
        '#theme' => 'intervenciones',
        '#data' => $intervenciones,
      );

      $table[] = array(
        '#theme' => 'departamentos_intervenciones_listado',
        '#data' => $data,
      );

      $mapa[] = array(
        '#theme' => 'provincias_mapa',
        '#data' => ['provincia' => $dpto_name],
      );
      return new JsonResponse([render($html),render($table),render($mapa)]);
    }else{
      return new RedirectResponse(\Drupal::url('/extranet/mapeo'));
    }
  }

  public function mapaInterFilterPorProvincias($parent_tid, $tid) {
    $intervenciones = [];
    $is_ajax = \Drupal::request()->isXmlHttpRequest();
    if($is_ajax){
      $provs = self::intervecionesProvinciaPorDepartamento($parent_tid);
      foreach ($provs as $key => $value) {
        if($value['tid'] == $tid){
          if(count($value['intervenciones']) > 0){
            foreach ($value['intervenciones'] as $k => $v) {
              $node = \Drupal\node\Entity\Node::load($v);
              $intervenciones['intervenciones'][] = ['title' => $node->title->value, 'nid' => $node->nid->value];
            }
          }
        }
      }
      $html[] = array(
        '#theme' => 'intervenciones',
        '#data' => $intervenciones,
      );
      return new JsonResponse([render($html)]);
    }else{
      return new RedirectResponse(\Drupal::url('/extranet/mapeo'));
    }
  }

  //Devuelve listado de departamentos y sus cantidades de intervenciones - MAPEO
  public static function departamentosIntervencionListado() {
    $data = [];
    $departamentos = self::intervecionesPorDepartamento();
    foreach ($departamentos as $key => $value) {
      if(strtolower($key) != strtolower('Nacional')){
        array_push($data, array('tid' => $value['tid'], 'departamento' => $key, 'cantidad' => count($value['intervenciones'])));
      }
    }
    $html[] = array(
      '#theme' => 'departamentos_intervenciones_listado',
      '#data' => $data,
    );

    return render($html);
  }

  private static function getCantInterByRegion($tid, $action) {
    $dpto_name = \Drupal\taxonomy\Entity\Term::load($tid)->getName();
    $result = [];
    $depths = [];
    $nodes =  self::getAllNodeTypes('intervencion', 'title', 'ASC');
    $ubigeo = self::getTaxonomyTree('ubigeo');

    foreach ($nodes as $node) {
      if(count($node->field_departamento) > 0){
        foreach ($node->field_departamento as $key => $value) {
          $result_query = db_query('SELECT * FROM paragraph__field_departamento WHERE entity_id = :pid', array(':pid' => $value->target_id));
          if ($result_query) {
            while ($row = $result_query->fetchAssoc()) {
              $tid_id = $row['field_departamento_target_id'];
              $terms = \Drupal::service('entity_type.manager')->getStorage("taxonomy_term")->loadAllParents($tid_id);
              if(count($terms) == 1){
                foreach ($terms as $t => $tv) {
                  foreach ($ubigeo as $k => $v) {
                    if($tv->tid->value == $v->tid){
                      $depa = \Drupal\taxonomy\Entity\Term::load($tv->tid->value)->getName();
                      $result[$node->nid->value][$depa][$v->name]['tid'] = $v->tid;
                      $result[$node->nid->value][$depa][$v->name]['depth'] = $v->depth;
                    }
                  }
                }
              }elseif(count($terms) == 2) {
                foreach ($terms as $t => $tv) {
                  foreach ($ubigeo as $k => $v) {
                    if($tv->tid->value == $v->tid){
                      if($v->depth == 1){
                        $depa = \Drupal\taxonomy\Entity\Term::load($v->parents[0])->getName();
                        $result[$node->nid->value][$depa][$v->name]['tid'] = $v->tid;
                        $result[$node->nid->value][$depa][$v->name]['depth'] = $v->depth;
                      }
                    }
                  }
                }
              }elseif(count($terms) == 3) {
                foreach ($terms as $t => $tv) {
                  foreach ($ubigeo as $k => $v) {
                    if($tv->tid->value == $v->tid){
                      if($v->depth == 1){
                        $depa = \Drupal\taxonomy\Entity\Term::load($v->parents[0])->getName();
                        $result[$node->nid->value][$depa][$v->name]['tid'] = $v->tid;
                        $result[$node->nid->value][$depa][$v->name]['depth'] = $v->depth;
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
    }

    $table = [];
    $compare_provs = [];
    foreach ($ubigeo as $value) {
      if($value->parents[0]){
        if($value->parents[0] == $tid){
          $compare_provs[$value->name] = [];
          foreach ($result as $de => $deps) {
            foreach ($deps as $pr => $pro) {
              foreach ($pro as $a => $p) {
                if($value->name == $a){
                  $table[$a][] = $de;
                }
                if($pr == $a){
                  $table[$a] = [$de];
                }
              }
            }
          }
        }
      }
    }

    foreach ($compare_provs as $key => $value) {
      foreach ($table as $k => $val) {
        if($key == $k){
          $compare_provs[$key] = $val;
        }
      }
    }

    if($action == 'cant'){
      foreach ($compare_provs as $key => $value) {
        if(count($value) > 0){
          $cant = ($cant + count($value));
        }
      }
      return $cant;
    }else{
      return $compare_provs;
    }
  }

  //Devuelve el estado - interveciones
  private static function checkEstadoIntervencion($nid) {
    $data = 'inter-state-draft';
    $sql = 'SELECT * FROM content_moderation_state_field_data WHERE content_entity_id = :entity_id';
    $filter = array(':entity_id' => $nid);
    $result = db_query($sql, $filter);
    if($result) {
      while ($row = $result->fetchAssoc()) {
        switch ($row['moderation_state']) {
          case 'draft':
            $data = 'inter-state-draft';
            break;
          case 'pendiente_de_revision':
            $data = 'inter-state-pr';
            break;
          case 'pendiente_de_correccion':
            $data = 'inter-state-pc';
            break;
          case 'aprobado':
            $data = 'inter-state-apro';
            break;
          case 'published':
            $data = 'inter-state-public';
            break;
        }
      }
    }
    return $data;
  }

  //Tools
  private static function sqlProccess($sql, $filter) {
    return db_query($sql, $filter);
  }

  private static function getAllNodeTypes($content_type,$filter_type,$filter_order) {
    $nids = \Drupal::entityQuery('node')
                    ->condition('type',$content_type)
                    ->sort($filter_type,$filter_orde)
                    ->execute();

    $nodes =  \Drupal\node\Entity\Node::loadMultiple($nids);
    return $nodes;
  }

  private static function getTaxonomyTree($vocabulary) {
    $taxonomy_vocabulary = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vocabulary);
    return $taxonomy_vocabulary;
  }

  public static function intervecionesPorDepartamento() {
  	$data = [];
  	$departamentos = [];
    $nodes =  self::getAllNodeTypes('intervencion', 'title', 'ASC');
  	$ubigeo = self::getTaxonomyTree('ubigeo');

  	foreach ($ubigeo as $ubi) {
  		if($ubi->depth == 0){
  			$departamentos[$ubi->name]['tid'] = $ubi->tid;
  			$departamentos[$ubi->name]['intervenciones'] = [];
  		}
  	}
  	foreach ($nodes as $node) {
  		if(count($node->field_departamento) > 0){
        foreach ($node->field_departamento as $key => $value) {
        	$contador = 0;
        	$result_query = self::sqlProccess('SELECT * FROM paragraph__field_departamento WHERE entity_id = :pid', array(':pid' => $value->target_id));
        	if ($result_query) {
  					while ($row = $result_query->fetchAssoc()) {
  						$tid_id = $row['field_departamento_target_id'];
  						$terms = \Drupal::service('entity_type.manager')->getStorage("taxonomy_term")->loadAllParents($tid_id);
  						if(count($terms) > 0){
  							foreach ($terms as $k => $v) {
  								$contador++;
  								if(count($terms) == $contador){
  									$depa = \Drupal\taxonomy\Entity\Term::load($v->tid->value);
  									$data[$node->nid->value][$depa->tid->value] = $depa->name->value;
  								}
  							}
  						}
  					}
	        }
        }
	    }
  	}

  	foreach ($departamentos as $d => $departamento) {
  		foreach ($data as $k => $v) {
  			foreach ($v as $key => $dpto) {
  				if($dpto == $d){
  					$departamentos[$dpto]['intervenciones'][] = $k;
  				}
  			}
  		}
  	}

  	return $departamentos;
  }

  public static function intervecionesProvinciaPorDepartamento($tid) {
    $dpto_name = \Drupal\taxonomy\Entity\Term::load($tid)->getName();
    $data = [];
    $provincias = [];
    $nodes =  self::getAllNodeTypes('intervencion', 'title', 'ASC');
    $ubigeo = self::getTaxonomyTree('ubigeo');

    foreach ($ubigeo as $ubi) {
      if($tid == $ubi->parents[0] && $ubi->depth == 1){
        $provincias[$ubi->name]['tid'] = $ubi->tid;
        $provincias[$ubi->name]['intervenciones'] = [];
      }
    }

    foreach ($nodes as $node) {
      if(count($node->field_departamento) > 0){
        foreach ($node->field_departamento as $key => $value) {
          $pro_tid = null;
          $pro_name = '';
          $contador = 0;
          $result_query = self::sqlProccess('SELECT * FROM paragraph__field_departamento WHERE entity_id = :pid', array(':pid' => $value->target_id));
          if ($result_query) {
            while ($row = $result_query->fetchAssoc()) {
              $tid_id = $row['field_departamento_target_id'];
              $terms = \Drupal::service('entity_type.manager')->getStorage("taxonomy_term")->loadAllParents($tid_id);
              if(count($terms) > 0){
                foreach ($terms as $k => $v) {
                  $contador++;
                  if(count($terms) == 1){
                    $pro_tid = $v->tid->value;
                    $pro_name = $v->name->value;
                  }
                  if(count($terms) == 2 && $contador == 1){
                    $pro_tid = $v->tid->value;
                    $pro_name = $v->name->value;
                  }
                  if(count($terms) == 3 && $contador == 2){
                    $pro_tid = $v->tid->value;
                    $pro_name = $v->name->value;
                  }
                  if(count($terms) == $contador){
                    $depa = \Drupal\taxonomy\Entity\Term::load($v->tid->value);
                    if($depa->tid->value == $tid){
                      $data[$node->nid->value][$pro_tid] = $pro_name;
                    }
                  }
                }
              }
            }
          }
        }
      }
    }

    foreach ($provincias as $p => $provincia) {
      foreach ($data as $k => $v) {
        foreach ($v as $key => $provi) {
          if($provi == $p){
            $provincias[$provi]['intervenciones'][$k] = $k;
          }elseif($provi == $dpto_name){
            $provincias['Regional']['tid'] = $tid;
            $provincias['Regional']['intervenciones'][$k] = $k;
          }
        }
      }
    }

    return $provincias;
  }

  private static function load($vocabulary) {
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vocabulary);
    $tree = [];
    foreach ($terms as $tree_object) {
      self::buildTree($tree, $tree_object, $vocabulary);
    }

    return $tree;
  }

  private static function buildTree(&$tree, $object, $vocabulary) {
    if ($object->depth != 0) {
      return;
    }
    $tree[$object->tid] = $object;
    $tree[$object->tid]->children = [];
    $object_children = &$tree[$object->tid]->children;

    $children = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadChildren($object->tid);
    if (!$children) {
      return;
    }

    $child_tree_objects = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vocabulary, $object->tid);

    foreach ($children as $child) {
      foreach ($child_tree_objects as $child_tree_object) {
        if ($child_tree_object->tid == $child->id()) {
         self::buildTree($object_children, $child_tree_object, $vocabulary);
        }
      }
    }
  }
}
