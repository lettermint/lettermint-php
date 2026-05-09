<?php

namespace Lettermint\Types;

/**
 * @phpstan-type ApiObject array<string, mixed>
 * @phpstan-type CursorPage array{data: list<ApiObject>, path: string|null, per_page: int, next_cursor: string|null, next_page_url: string|null, prev_cursor: string|null, prev_page_url: string|null}
 * @phpstan-type SendMailRequest array{from: string, subject: string, to: list<string>, route?: string, tag?: string|null, html?: string|null, text?: string|null, cc?: list<string>, bcc?: list<string>, reply_to?: list<string>, headers?: array<string, string>, metadata?: array<string, string>, settings?: array{track_opens?: bool, track_clicks?: bool}|null, attachments?: list<array{filename: string, content: string, content_type?: string|null, content_id?: string|null}>}
 * @phpstan-type SendBatchMailRequest list<SendMailRequest>
 * @phpstan-type SendMailResponse array{message_id: string, status: string}
 * @phpstan-type SendBatchMailResponse list<SendMailResponse>
 * @phpstan-type StoreDomainData array{domain: string}
 * @phpstan-type UpdateDomainProjectsData array{project_ids: non-empty-list<string>}
 * @phpstan-type StoreProjectData array{name: string, smtp_enabled?: bool, initial_routes?: 'both'|'transactional'|'broadcast'}
 * @phpstan-type UpdateProjectData array{name?: string|null, smtp_enabled?: bool|null, default_route_id?: string|null}
 * @phpstan-type UpdateProjectMembersData array{team_member_ids: non-empty-list<string>}
 * @phpstan-type StoreRouteData array{name: string, route_type: 'transactional'|'broadcast'|'inbound', slug?: string|null}
 * @phpstan-type UpdateRouteData array{name?: string|null, settings?: array{track_opens?: bool|null, track_clicks?: bool|null, disable_hosted_unsubscribe?: bool|null}, inbound_settings?: array{inbound_domain?: string|null, inbound_spam_threshold?: float|int|null, attachment_delivery?: 'inline'|'url'}}
 * @phpstan-type StatsQuery array{from: string, to: string, project_id?: string|null, include_machine?: bool}
 * @phpstan-type StoreSuppressionData array{reason: 'spam_complaint'|'hard_bounce'|'unsubscribe'|'manual', scope: 'team'|'project'|'route', email?: string|null, emails?: non-empty-list<string>|null, route_id?: string|null, project_id?: string|null}
 * @phpstan-type UpdateTeamData array{name?: string|null}
 * @phpstan-type StoreWebhookData array{route_id: string, name: string, url: string, events: non-empty-list<string>, enabled?: bool|null, include_machine_events?: bool|null}
 * @phpstan-type UpdateWebhookData array{name?: string, url?: string, enabled?: bool, include_machine_events?: bool, events?: non-empty-list<string>}
 */
final class ApiTypes {}
