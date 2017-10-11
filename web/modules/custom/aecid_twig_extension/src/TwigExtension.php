<?php

namespace Drupal\aecid_twig_extension;

use Drupal\block\Entity\Block;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;

use Drupal\intervenciones\Controller\ListaIntervecionController;

/**
 * Class DefaultService.
 *
 * @package Drupal\aecid_twig_extension
 */
class TwigExtension extends \Twig_Extension {
  /**
   * {@inheritdoc}
   * This function must return the name of the extension. It must be unique.
   */
  public function getName() {
    return 'twig_extension_aecid';
  }

  /**
   * In this function we can declare the extension function.
   */
  public function getFunctions() {
    return array(
      new \Twig_SimpleFunction('getStateIntervencion', array($this, 'getStateIntervencion'), array('is_safe' => array('html'))),
      new \Twig_SimpleFunction('getObervacionIntervencion', array($this, 'getObervacionIntervencion'), array('is_safe' => array('html'))),
      new \Twig_SimpleFunction('getCodeCodigosCad', array($this, 'getCodeCodigosCad'), array('is_safe' => array('html'))),
    );
  }

  public function getStateIntervencion($nid) {
    $state = ListaIntervecionController::estadoIntervencion($nid);
    return $state;
  }

  public function getObervacionIntervencion($nid) {
    $observacion = ListaIntervecionController::observacionIntervencion($nid);
    return $observacion;
  }

  public function getCodeCodigosCad($tid) {
    $codigo = '';
    $result = db_query('SELECT * FROM taxonomy_term__field_codigo_cad WHERE entity_id = :entity_id', array(':entity_id' => $tid));
    if($result){
      while ($row = $result->fetchAssoc()) {
        $codigo = $row['field_codigo_cad_value'];
      }
    }
    return $codigo;
  }
}
