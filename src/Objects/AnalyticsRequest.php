<?php

namespace Lettermint\Objects;

use Lettermint\Resource;

/**
 * @property list<'accepted'|'processed'|'suppressed'|'policy_rejected'|'application_failed'|'mta_accepted'|'canceled'|'messages'|'delivered'|'bounced'|'soft_bounced'|'administratively_bounced'|'deferred_recipients'|'deferred_events'|'delivery_attempts'|'attempted_recipients'|'transport_outcome_recipients'|'effective_delivered'|'open_tracked_delivered'|'click_tracked_delivered'|'out_of_band_bounced_recipients'|'out_of_band_bounce_events'|'complained'|'unsubscribed'|'human_opens'|'human_opens_events'|'human_clicks'|'human_clicks_events'|'machine_opens'|'machine_opens_events'|'machine_clicks'|'machine_clicks_events'|'privacy_opens'|'privacy_opens_events'|'privacy_clicks'|'privacy_clicks_events'|'bot_opens'|'bot_opens_events'|'bot_clicks'|'bot_clicks_events'|'scanner_opens'|'scanner_opens_events'|'scanner_clicks'|'scanner_clicks_events'|'observed_opens'|'observed_opens_events'|'observed_clicks'|'observed_clicks_events'|'delivery_rate'|'effective_delivery_rate'|'bounce_rate'|'deferral_rate'|'complaint_rate'|'human_open_rate'|'human_click_rate'|'processing_latency_p50_ms'|'processing_latency_p95_ms'|'processing_latency_p99_ms'|'processing_latency_samples'|'delivery_latency_p50_ms'|'delivery_latency_p95_ms'|'delivery_latency_p99_ms'|'delivery_latency_samples'|'total_latency_p50_ms'|'total_latency_p95_ms'|'total_latency_p99_ms'|'total_latency_samples'> $metrics
 * @property string $from
 * @property string $to
 * @property string $timezone
 * @property list<'summary'|'time_series'|'breakdown'> $include
 * @property list<string> $group_by
 * @property list<array<string, mixed>> $filters
 * @property 'hour'|'day' $interval
 * @property 'previous_period' $compare
 * @property bool $include_trend
 * @property array<string, mixed> $sort
 * @property int $limit
 * @property string $cursor
 */
final class AnalyticsRequest extends Resource
{
    //
}
