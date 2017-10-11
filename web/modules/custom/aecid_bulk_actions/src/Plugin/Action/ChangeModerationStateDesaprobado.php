<?php

namespace Drupal\aecid_bulk_actions\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Cambia el Estado de la Intervencion - Desaprobado.
 *
 * @Action(
 *   id = "change_moderation_state_desaprobado",
 *   label = @Translation("Cambia el Estado de la Intervencion - Desaprobado"),
 *   type = "node"
 * )
 */

class ChangeModerationStateDesaprobado extends ActionBase {
  /**
   * {@inheritdoc}
   */
  public function execute($node = NULL) {
    if ($node) {
      /** @var \Drupal\node\NodeInterface $node */
      $node->setPublished(FALSE);
      $node->set('moderation_state', 'pendiente_de_correccion');
      $node->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\node\NodeInterface $object */
    $result = $object->access('update', $account, TRUE);

    return $return_as_object ? $result : $result->isAllowed();
  }
}
