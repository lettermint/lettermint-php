<?php

namespace Lettermint\Types;

/**
 * This file is generated.
 *
 * @phpstan-type ApiObject array<string, mixed>
 * @phpstan-type CursorPage array{data: list<ApiObject>, path: string|null, per_page: int, next_cursor: string|null, next_page_url: string|null, prev_cursor: string|null, prev_page_url: string|null}
 * @phpstan-type MessageStatus 'pending'|'queued'|'suppressed'|'processed'|'delivered'|'opened'|'clicked'|'soft_bounced'|'hard_bounced'|'spam_complaint'|'failed'|'blocked'|'policy_rejected'|'unsubscribed'
 * @phpstan-type SendMailRequest array{route?: string, from: string, subject: string, tag?: string|null, html?: string|null, text?: string|null, to: non-empty-list<string>, cc?: list<string>, bcc?: list<string>, reply_to?: list<string>, headers?: array<string, string>, metadata?: array<string, string>, settings?: array{track_opens?: bool, track_clicks?: bool}|null, attachments?: list<array{filename: string, content: string, content_type?: string|null, content_id?: string|null}>}
 * @phpstan-type SendBatchMailRequest list<SendMailRequest>
 * @phpstan-type AttachmentDelivery 'inline'|'url'
 * @phpstan-type DnsRecordStatus 'active'|'failed'|'pending'
 * @phpstan-type DomainData array{id: string, domain: string, status_changed_at: string|null, dns_records?: list<DomainDnsRecordData>, projects?: list<array{id: string, name: string}>, created_at: string}
 * @phpstan-type DomainDnsRecordData array{id: string, type: RecordType, hostname: string, fqdn: string, content: string, status: DnsRecordStatus, verified_at: string|null, last_checked_at: string|null}
 * @phpstan-type DomainListData array{id: string, domain: string, status: DomainStatus, status_changed_at: string|null, created_at: string}
 * @phpstan-type DomainStatus 'verified'|'partially_verified'|'pending_verification'|'failed_verification'
 * @phpstan-type InitialRoutes 'both'|'transactional'|'broadcast'
 * @phpstan-type MessageAttachmentData array{size: int, filename: string, content_id: string|null, content_type: string}
 * @phpstan-type MessageData array{id: string, type: MessageType, status: MessageStatus, status_changed_at: string|null, tag: string|null, from_email: string, from_name: string|null, reply_to: list<string>|null, subject: string|null, to: list<MessageRecipientData>|null, cc: list<MessageRecipientData>|null, bcc: list<MessageRecipientData>|null, attachments: list<MessageAttachmentData>|null, metadata: array<string, string>|null, spam_score?: float|int|null, spam_symbols?: list<SpamSymbol>, route_id: string, created_at: string}
 * @phpstan-type MessageEventData array{message_id: string, event: MessageEventType, metadata: array<string, mixed>|null, timestamp: string}
 * @phpstan-type MessageEventType 'queued'|'processed'|'suppressed'|'delivered'|'soft_bounced'|'hard_bounced'|'spam_complaint'|'failed'|'blocked'|'policy_rejected'|'unsubscribed'|'opened'|'clicked'|'inbound_received'|'inbound_queued'|'inbound_spam_blocked'|'inbound_processed'|'inbound_retry'
 * @phpstan-type MessageListData array{id: string, type: MessageType, status: MessageStatus, from_email: string, from_name: string|null, subject: string|null, to: list<MessageRecipientData>|null, cc: list<MessageRecipientData>|null, bcc: list<MessageRecipientData>|null, reply_to: list<string>|null, tag: string|null, created_at: string}
 * @phpstan-type MessageRecipientData array{email: string, name: string|null}
 * @phpstan-type MessageStatsData array{messages_transactional: int, messages_broadcast: int, messages_inbound: int, deliverability: float|int}
 * @phpstan-type MessageType 'inbound'|'outbound'
 * @phpstan-type Plan 'free'|'starter'|'growth'|'pro'
 * @phpstan-type ProjectData array{id: string, name: string, smtp_enabled: bool, default_route_id: string|null, token_generated_at: string|null, token_last_used_at: string|null, token_last_used_ip: string|null, routes?: list<RouteListData>, routes_count?: int, domains?: list<DomainData>, domains_count?: int, team_members?: list<TeamMemberData>, team_members_count?: int, last_28_days?: MessageStatsData|null, created_at: string, updated_at: string}
 * @phpstan-type ProjectListData array{id: string, name: string, smtp_enabled: bool, routes_count: int, domains_count: int, team_members_count: int, last_28_days: MessageStatsData, created_at: string, updated_at: string}
 * @phpstan-type RecordType 'TXT'|'CNAME'|'MX'
 * @phpstan-type RouteData array{id: string, project_id: string, slug: string, name: string, route_type: RouteType, is_default: bool, inbound_address?: string, inbound_domain?: string, inbound_domain_verified_at?: string, inbound_spam_threshold?: float|int, attachment_delivery?: AttachmentDelivery, project?: ProjectListData, webhooks_count?: int, suppressed_recipients_count?: int, statistics?: array<string, mixed>|list<RouteStatisticData>, created_at: string, updated_at: string}
 * @phpstan-type RouteListData array{id: string, slug: string, name: string, route_type: RouteType, is_default: bool, webhooks_count: int, suppressed_recipients_count: int, created_at: string, updated_at: string}
 * @phpstan-type RouteStatisticData array{date: string, sent_count: int, delivered_count: int, opened_count: int, clicked_count: int, hard_bounce_count: int, spam_complaint_count: int, inbound_received_count: int, effective_opened_count: int|null, machine_opened_count: int|null, machine_clicked_count: int|null}
 * @phpstan-type RouteType 'transactional'|'broadcast'|'inbound'
 * @phpstan-type SpamSymbol array{name: string, score: float|int, options: non-empty-list<string>, description: string|null}
 * @phpstan-type StatsDailyData array{date: string, sent: int, delivered: int, hard_bounced: int, spam_complaints: int, opened: int|null, clicked: int|null, inbound: StatsInboundData, transactional: StatsTypeData|null, broadcast: StatsTypeData|null, effective_opened: int|null, machine_opened: int|null, machine_clicked: int|null}
 * @phpstan-type StatsData array{from: string, to: string, totals: StatsTotalsData, daily: list<StatsDailyData>}
 * @phpstan-type StatsInboundData array{received: int}
 * @phpstan-type StatsRequestData array{from: string, to: string, project_id?: string|null, include_machine?: bool}
 * @phpstan-type StatsTotalsData array{sent: int, delivered: int, hard_bounced: int, spam_complaints: int, opened: int|null, clicked: int|null, inbound: StatsInboundData, transactional: StatsTypeData|null, broadcast: StatsTypeData|null, effective_opened: int|null, machine_opened: int|null, machine_clicked: int|null}
 * @phpstan-type StatsTypeData array{sent: int, hard_bounced: int, spam_complaints: int}
 * @phpstan-type StoreDomainData array{domain: string}
 * @phpstan-type StoreProjectData array{name: string, smtp_enabled?: bool, initial_routes?: InitialRoutes}
 * @phpstan-type StoreRouteData array{name: string, route_type: RouteType, slug?: string|null}
 * @phpstan-type StoreSuppressionData array{email?: string|null, reason: SuppressionReason, scope: 'team'|'project'|'route', route_id?: string|null, project_id?: string|null, emails?: non-empty-list<string>|null}
 * @phpstan-type StoreWebhookData array{route_id: string, name: string, url: string, enabled?: bool|null, include_machine_events?: bool|null, events: non-empty-list<WebhookEvent>}
 * @phpstan-type SuppressedRecipientData array{id: string, type: SuppressionType, value: string, reason: SuppressionReason, scope: SuppressionScope, project_id: string|null, route_id: string|null, created_at: string, updated_at: string}
 * @phpstan-type SuppressionReason 'spam_complaint'|'hard_bounce'|'unsubscribe'|'manual'
 * @phpstan-type SuppressionScope 'global'|'team'|'project'|'route'
 * @phpstan-type SuppressionType 'email'|'domain'|'extension'
 * @phpstan-type TeamAddonData array{type: string|null, expires_at: string|null}
 * @phpstan-type TeamData array{id: string, name: string, type: TeamType, plan: Plan, tier: VolumeTier, verified_at: string|null, features?: list<string>, addons?: list<TeamAddonData>, created_at: string, domains_count?: int, projects_count?: int, members_count?: int}
 * @phpstan-type TeamMemberData array{id: string, user?: UserData, role: string|null, joined_at: string|null}
 * @phpstan-type TeamType 'personal'|'business'
 * @phpstan-type TeamUsageDetailData array{current_period: TeamUsagePeriodData, historical_usage: list<TeamUsagePeriodData>}
 * @phpstan-type TeamUsagePeriodData array{usage: int, last_incremented_at: string|null, period_start: string, period_end: string}
 * @phpstan-type UpdateDomainProjectsData array{project_ids: non-empty-list<string>}
 * @phpstan-type UpdateProjectData array{name?: string|null, smtp_enabled?: bool|null, default_route_id?: string|null}
 * @phpstan-type UpdateProjectMembersData array{team_member_ids: non-empty-list<string>}
 * @phpstan-type UpdateRouteData array{name?: string|null, settings?: array{track_opens?: bool|null, track_clicks?: bool|null, disable_hosted_unsubscribe?: bool|null}, inbound_settings?: array{inbound_domain?: string|null, inbound_spam_threshold?: float|int|null, attachment_delivery?: AttachmentDelivery}}
 * @phpstan-type UpdateTeamData array{name?: string|null}
 * @phpstan-type UpdateWebhookData array{name?: string, url?: string, enabled?: bool, include_machine_events?: bool, events?: non-empty-list<WebhookEvent>}
 * @phpstan-type UserData array{id: string, name: string, email: string, avatar: string|null}
 * @phpstan-type VolumeTier 300|10000|50000|125000|500000|750000|1000000|1500000
 * @phpstan-type WebhookData array{id: string, route_id: string, name: string, url: string, events: non-empty-list<string>, enabled: bool, include_machine_events: bool, secret?: string, last_called_at: string|null, created_at: string, updated_at: string}
 * @phpstan-type WebhookDeliveryData array{id: string, webhook_id: string, event_type: WebhookEvent, status: WebhookDeliveryStatus, attempt_number: int, http_status_code: int|null, duration_ms: int|null, payload: non-empty-list<string>, response_body: string|null, response_headers: list<string>|null, error_message: string|null, delivered_at: string|null, timestamp: string}
 * @phpstan-type WebhookDeliveryListData array{id: string, webhook_id: string, event_type: WebhookEvent, status: WebhookDeliveryStatus, attempt_number: int, http_status_code: int|null, duration_ms: int|null, delivered_at: string|null, created_at: string}
 * @phpstan-type WebhookDeliveryStatus 'pending'|'success'|'failed'|'client_error'|'server_error'|'timeout'
 * @phpstan-type WebhookEvent 'message.created'|'message.sent'|'message.delivered'|'message.hard_bounced'|'message.soft_bounced'|'message.spam_complaint'|'message.failed'|'message.suppressed'|'message.unsubscribed'|'message.opened'|'message.clicked'|'message.inbound'|'message.policy_rejected'|'webhook.test'
 * @phpstan-type WebhookListData array{id: string, route_id: string, name: string, url: string, events: list<WebhookEvent>, enabled: bool, last_called_at: string|null, created_at: string, updated_at: string}
 * @phpstan-type StatsQuery StatsRequestData
 * @phpstan-type SendMailResponse array{message_id: string, status: MessageStatus}
 * @phpstan-type SendBatchMailResponse list<SendMailResponse>
 * @phpstan-type DomainListResponse array{data: list<DomainListData>, path: string|null, per_page: int, next_cursor: string|null, next_page_url: string|null, prev_cursor: string|null, prev_page_url: string|null}
 * @phpstan-type DomainResponse DomainData
 * @phpstan-type DeleteDomainResponse array{message: string}
 * @phpstan-type VerifyDnsRecordsResponse array{message: string}
 * @phpstan-type VerifyDnsRecordResponse array{message: string}
 * @phpstan-type UpdateDomainProjectsResponse array{data: DomainData, message: string}
 * @phpstan-type MessageListResponse array{data: list<MessageListData>, path: string|null, per_page: int, next_cursor: string|null, next_page_url: string|null, prev_cursor: string|null, prev_page_url: string|null}|list<MessageListData>
 * @phpstan-type MessageResponse MessageData
 * @phpstan-type MessageEventsResponse array{data: list<MessageEventData>, path: string|null, per_page: int, next_cursor: string|null, next_page_url: string|null, prev_cursor: string|null, prev_page_url: string|null}
 * @phpstan-type ProjectListResponse array{data: list<ProjectListData>, path: string|null, per_page: int, next_cursor: string|null, next_page_url: string|null, prev_cursor: string|null, prev_page_url: string|null}
 * @phpstan-type CreateProjectResponse array{data: ProjectData, message: string, api_token: string}
 * @phpstan-type ProjectResponse ProjectData
 * @phpstan-type UpdateProjectResponse array{data: ProjectData, message: string}
 * @phpstan-type DeleteProjectResponse array{message: string}
 * @phpstan-type RotateProjectTokenResponse array{data: ProjectData, new_token: string, message: string}
 * @phpstan-type UpdateProjectMembersResponse array{data: ProjectData, message: string}
 * @phpstan-type ProjectMemberResponse array{message: string}
 * @phpstan-type ProjectRoutesResponse array{data: list<RouteListData>, path: string|null, per_page: int, next_cursor: string|null, next_page_url: string|null, prev_cursor: string|null, prev_page_url: string|null}
 * @phpstan-type CreateRouteResponse array{data: RouteData, message: string}
 * @phpstan-type RouteResponse RouteData
 * @phpstan-type UpdateRouteResponse array{data: RouteData, message: string}
 * @phpstan-type DeleteRouteResponse array{message: string}
 * @phpstan-type VerifyInboundDomainResponse array{data: array{verified: bool, message: string}}
 * @phpstan-type StatsResponse StatsData
 * @phpstan-type SuppressionListResponse array{data: list<SuppressedRecipientData>, path: string|null, per_page: int, next_cursor: string|null, next_page_url: string|null, prev_cursor: string|null, prev_page_url: string|null}
 * @phpstan-type CreateSuppressionResponse array{message: string|'No emails were added.', data: array{created: list<string>, skipped: list<string>}}
 * @phpstan-type DeleteSuppressionResponse array{message: string}
 * @phpstan-type TeamResponse TeamData
 * @phpstan-type UpdateTeamResponse array{data: TeamData, message: string}
 * @phpstan-type TeamUsageResponse TeamUsageDetailData
 * @phpstan-type TeamMembersResponse array{data: list<TeamMemberData>, path: string|null, per_page: int, next_cursor: string|null, next_page_url: string|null, prev_cursor: string|null, prev_page_url: string|null}
 * @phpstan-type WebhookListResponse array{data: list<WebhookListData>, path: string|null, per_page: int, next_cursor: string|null, next_page_url: string|null, prev_cursor: string|null, prev_page_url: string|null}
 * @phpstan-type CreateWebhookResponse array{data: WebhookData, message: string}
 * @phpstan-type WebhookResponse WebhookData
 * @phpstan-type UpdateWebhookResponse array{data: WebhookData, message: string}
 * @phpstan-type DeleteWebhookResponse array{message: string}
 * @phpstan-type TestWebhookResponse array{message: string, delivery_id: string}
 * @phpstan-type RegenerateWebhookSecretResponse array{data: WebhookData, message: string}
 * @phpstan-type WebhookDeliveriesResponse array{data: list<WebhookDeliveryListData>, path: string|null, per_page: int, next_cursor: string|null, next_page_url: string|null, prev_cursor: string|null, prev_page_url: string|null}
 * @phpstan-type WebhookDeliveryResponse WebhookDeliveryData
 */
final class ApiTypes {}
