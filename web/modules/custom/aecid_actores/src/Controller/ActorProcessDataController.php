<?php

namespace Drupal\aecid_actores\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class ActorProcessDataController.
 */
class ActorProcessDataController extends ControllerBase {
  /**
   * Devuelve Actores segun la primera letra
   */
  public static function getActorsByLetter() {
    return '';
  }

  /**
   * Devuelve Listado de Actores segun su tipo [Español, Internaciona, Peruano]
   */
  public static function getDataByActorType($actorType) {
    $data = null;
    switch ($actorType) {
      case 'espanol':
        $data = self::dataActorEspanol();
        break;

      case 'internacional':
        $data = self::dataActorInternacional();
        break;

      case 'peruano':
        $data = self::dataActorPeruano();
        break;
    }

    return $data;
  }

  /**
   * Devuelve la Data de Actores Españoles
   */
  private static function dataActorEspanol() {
    return self::preProccessDataUserProfile('espanoles');
  }

  /**
   * Devuelve la Data de Actores Internacionales
   */
  private static function dataActorInternacional() {
    return self::preProccessDataUserProfile('internacionales');
  }

  /**
   * Devuelve la Data de Actores Peruanos
   */
  private static function dataActorPeruano() {
    return self::preProccessDataUserProfile('peruanos');
  }

  private static function preProccessDataUserProfile($pt) {
    $data = [];
    $actor_tipos = self::getActorTypes();
    $nodes =  self::getAllNodeTypes('actor_espanol', 'title', 'ASC');
    foreach ($nodes as $n => $node) {
      $user_acronimo = '';
      $user_nombre_largo = '';

      if($node->field_acronimo){
        $user_acronimo = $node->field_acronimo->value . ' - ';
      }

      $user_nombre_largo = $node->title->value;

      if($node->field_tipo){
        foreach ($actor_tipos as $at => $actor_tipo) {
          if($node->field_tipo->target_id == $at && $actor_tipo['depth'] == 0){
            $monto_can = '0.00';
            $monto_fin = '0.00';
            $monto_eje = '0.00';
            $nro_inter_1 = '0';
            $nro_inter_2 = '0';
            $nro_inter_3 = '0';
            $data[$actor_tipo['name']]['Otras entidades'][] = [
                                                                'letter_init' => strtolower(substr($user_acronimo.$user_nombre_largo, 0, 1)),
                                                                'name' => $user_acronimo.$user_nombre_largo,
                                                                'nid' => $node->nid->value,
                                                                'show' => $node->field_mostrar->value,
                                                                'monto_cana' => $monto_can,
                                                                'nro_inter_1' => $nro_inter_1,
                                                                'monto_fin' => $monto_fin,
                                                                'nro_inter_2' => $nro_inter_2,
                                                                'monto_eje' => $monto_eje,
                                                                'nro_inter_3' => $nro_inter_3];
          }elseif($node->field_tipo->target_id == $at && $actor_tipo['depth'] == 1){
            $monto_can = '0.00';
            $monto_fin = '0.00';
            $monto_eje = '0.00';
            $nro_inter_1 = '0';
            $nro_inter_2 = '0';
            $nro_inter_3 = '0';
            $data[$actor_tipo['parent_name']][$actor_tipo['name']][] = [
                                                                          'letter_init' => strtolower(substr($user_acronimo.$user_nombre_largo, 0, 1)),
                                                                          'name' => $user_acronimo.$user_nombre_largo,
                                                                          'nid' => $node->nid->value,
                                                                          'show' => $node->field_mostrar->value,
                                                                          'monto_cana' => $monto_can,
                                                                          'nro_inter_1' => $nro_inter_1,
                                                                          'monto_fin' => $monto_fin,
                                                                          'nro_inter_2' => $nro_inter_2,
                                                                          'monto_eje' => $monto_eje,
                                                                          'nro_inter_3' => $nro_inter_3];
          }
        }
      }
    }
    return $data;
  }

  private static function getActorTypes(){
    $data = [];
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('tipo_actor');
    foreach ($terms as $key => $value) {
      if($value->depth == 0){
        $data[$value->tid] = ['depth' => $value->depth, 'name' => $value->name];
      }elseif($value->depth == 1){
        $parent = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($value->parents[0]);
        $parent_name = $parent->name->value;
        $data[$value->tid] = ['depth' => $value->depth, 'name' => $value->name, 'parent' => $value->parents[0], 'parent_name' => $parent_name];
      }
    }
    return $data;
  }

  private static function getAllNodeTypes($content_type,$filter_type,$filter_order) {
    $nids = \Drupal::entityQuery('node')
                    ->condition('type',$content_type)
                    ->sort($filter_type,$filter_orde)
                    ->execute();

    $nodes =  \Drupal\node\Entity\Node::loadMultiple($nids);
    return $nodes;
  }
}
