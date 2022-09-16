<?php

namespace Drupal\unpd_cop\Plugin\Action;

use Drupal\social_group_invite\Plugin\Action\SocialGroupInviteResend;

/**
 * Resend invites for NON group members.
 *
 *  This action allows resending membership invitations to users that not
 * accepted the invitation yet.
 *
 * @Action(
 *   id = "social_group_invite_resend_action_non_members",
 *   label = @Translation("Resend invites for NON-group members"),
 *   type = "group_content",
 *   confirm = TRUE,
 * )
 */
class SocialGroupInviteResendNonMembers extends SocialGroupInviteResend {

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function execute($entity = NULL): void {
    $is_member = $entity->getGroup()->getMember($entity->getEntity());

    if (!is_object($is_member)) {
      parent::execute($entity);
    }
  }

}
