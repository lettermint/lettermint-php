<?php

declare(strict_types=1);

namespace Lettermint\Resources;

use Generator;
use Lettermint\Types\ListTeamInvitesResponse;
use Lettermint\Types\MessageResponse;
use Lettermint\Types\TeamInviteData;

/**
 * Pending team invitations. Needs the team token.
 *
 * @phpstan-import-type ListTeamInvitesQuery from \Lettermint\Types\ApiTypes
 * @phpstan-import-type CreateTeamInviteData from \Lettermint\Types\ApiTypes
 * @phpstan-import-type UpdateTeamInviteData from \Lettermint\Types\ApiTypes
 */
final class TeamInvites extends Resource
{
    /**
     * Lists pending invitations, one page at a time.
     *
     * @param  ListTeamInvitesQuery  $query
     */
    public function list(array $query = []): ListTeamInvitesResponse
    {
        return $this->transport->object(ListTeamInvitesResponse::class, 'GET /team/invites', 'team.invites.list', query: $query);
    }

    /**
     * Iterates over every pending invitation, following `next_cursor`.
     *
     * @param  ListTeamInvitesQuery  $query
     * @return Generator<int, TeamInviteData, mixed, void>
     */
    public function iterate(array $query = []): Generator
    {
        return $this->transport->paginate(ListTeamInvitesResponse::class, 'GET /team/invites', 'team.invites.iterate', query: $query);
    }

    public function retrieve(string $inviteId): TeamInviteData
    {
        return $this->transport->object(TeamInviteData::class, 'GET /team/invites/{inviteId}', 'team.invites.retrieve', ['inviteId' => $inviteId]);
    }

    /**
     * Invites a person to the team and sends the invitation email. The team
     * needs a free seat; the Owner role cannot be assigned.
     *
     * @param  CreateTeamInviteData  $body
     */
    public function create(array $body): TeamInviteData
    {
        return $this->transport->object(TeamInviteData::class, 'POST /team/invites', 'team.invites.create', json: $body);
    }

    /**
     * Replaces the email, role and project access of a pending invitation. A
     * new invitation email is sent only when the email address changes.
     *
     * @param  UpdateTeamInviteData  $body
     */
    public function update(string $inviteId, array $body): TeamInviteData
    {
        return $this->transport->object(TeamInviteData::class, 'PUT /team/invites/{inviteId}', 'team.invites.update', ['inviteId' => $inviteId], json: $body);
    }

    /**
     * Revokes a pending invitation.
     */
    public function revoke(string $inviteId): MessageResponse
    {
        return $this->transport->object(MessageResponse::class, 'DELETE /team/invites/{inviteId}', 'team.invites.revoke', ['inviteId' => $inviteId]);
    }
}
