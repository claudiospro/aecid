<?php

namespace Drupal\aecid_bulk_actions\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Elimina la Intervencion - Eliminar.
 *
 * @Action(
 *   id = "change_moderation_state_eliminar",
 *   label = @Translation("Elimina la Intervencion - Eliminar"),
 *   type = "node"
 * )
 */

class ChangeModerationStateEliminar extends ActionBase {
  /**
   * {@inheritdoc}
   */
  public function execute($node = NULL) {
    if ($node) {
      /** @var \Drupal\node\NodeInterface $node */
      $node->delete();
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
