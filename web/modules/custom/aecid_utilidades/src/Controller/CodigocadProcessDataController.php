<?php

namespace Drupal\aecid_utilidades\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class CodigocadProcessDataController.
 */
class CodigocadProcessDataController extends ControllerBase {

  public static function getCodigoCadByIdForExport($tid) {
    $data = [];
    $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
    $term_title = $term->name->value;
    $term_childs = self::getAllCodigoCadForExport();

    foreach ($term_childs['items'] as $term_child) {
      if($term_child['cgcode'] == $tid){
        $data['items'][] = $term_child;
      }
    }

    return $data;
  }

  public static function getAllCodigoCadForExport() {
    $data = [];
    $taxonomy_tree = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('codigos_cad');
    foreach ($taxonomy_tree as $tk => $term) {
      $cgcode = '';
      $cgdesc = '';
      $cecode = '';
      $cedesc = '';
      $crcode = '';
      $crdesc = '';
      if($term->depth == 2){
        $crcode = $term->tid;
        $crdesc = $term->name;
        $t1 = self::getCodigoCadIntoVocabulary($term->parents[0]);
        $cecode = $t1->tid;
        $cedesc = $t1->name;
        $t01 = self::getCodigoCadIntoVocabulary($t1->parents[0]);
        $cgcode = $t01->tid;
        $cgdesc = $t01->name;
      }elseif($term->depth == 1){
        $cecode = $term->tid;
        $cedesc = $term->name;
        $t0 = self::getCodigoCadIntoVocabulary($term->parents[0]);
        $cgcode = $t0->tid;
        $cgdesc = $t0->name;
      }elseif($term->depth == 0){
        $cgcode = $term->tid;
        $cgdesc = $term->name;
      }
      $data['items'][] = ['cgcode' => $cgcode, 'cgdesc' => $cgdesc, 'cecode' => $cecode, 'cedesc' => $cedesc, 'crcode' => $crcode, 'crdesc' => $crdesc];
    }

    return $data;
  }

  private static function getCodigoCadIntoVocabulary($tid) {
    $data = null;
    $taxonomy_tree = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('codigos_cad');
    foreach ($taxonomy_tree as $value) {
      if($value->tid == $tid){
        $data = $value;
      }
    }
    return $data;
  }

  /*
  =====================================
  = Procesa el Código CAD por su (TID)
  =====================================
  */
  public static function getCodigoCadById($tid) {
    $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
    $term_title = $term->name->value;
    $term_childs = self::getTermChilds($tid);
    $data = array(
      'term_tid' => $tid,
      'term_title' => $term_title,
      'term_childs' => $term_childs
    );
    $content[] = array(
      '#theme' => 'aecid_codigo_cad_popup',
      '#data' => $data,
    );
    $build[] = array(
      '#theme' => 'aecid_lightbox',
      '#content' => render($content),
    );
    return array('data' => render($build));
  }

  /*
  ===================================
  = Procesa el listado de Códigos CAD
  ===================================
  */
  public static function getCodigosCad() {
    return self::dataCodigoCad();
  }

  /*
  =================================
  = Procesa y elimina un Código CAD
  =================================
  */
  public static function checkDeleteCodigoCadById($tid) {
    $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
    $term_childs = self::getTermChilds($tid);
    $response = [];
    if(count($term_childs) > 0){
      $response = ['response' => 'false'];
    }else{
      $response = ['response' => 'true'];
    }
    return $response;
  }
  public static function deleteCodigoCadById($tid) {
    $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
    $term_childs = self::getTermChilds($tid);
    $response = [];
    if(count($term_childs) > 0){
      foreach ($term_childs as $child) {
        \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($child->tid)->delete();
      }
      $term->delete();
    }else{
      $term->delete();
      $response = ['response' => 'true'];
    }
    return $response;
  }

  /*
  ==============================================
  = Arma todo el arbol (tree) de los Códigos CAD
  ==============================================
  */
  private static function dataCodigoCad() {
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('codigos_cad');
    $tree = [];

    foreach ($terms as $tree_object) {
      self::buildTree($tree, $tree_object, 'codigos_cad');
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

  /*
  ==========================================
  = Procesa todos los hijos de un Código CAD
  ==========================================
  */
  private static function getTermChilds($tid) {
    $data = [];
    $childs = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('codigos_cad');
    foreach ($childs as $key => $child) {
      if($child->parents[0] == $tid){
        array_push($data, $child);
      }
    }
    return $data;
  }
}
