<?php

declare(strict_types=1);

namespace Lettermint\Resources;

use Lettermint\Types\GetReportForwardingResponse;
use Lettermint\Types\ResendReportForwardingCodeResponse;
use Lettermint\Types\UpdateReportForwardingResponse;
use Lettermint\Types\VerifyReportForwardingResponse;

/**
 * DMARC and complaint report forwarding of a project. Needs the team token.
 *
 * @phpstan-import-type ReportForwardingRequest from \Lettermint\Types\ApiTypes
 * @phpstan-import-type VerifyReportForwardingRequest from \Lettermint\Types\ApiTypes
 */
final class ReportForwarding extends Resource
{
    public function retrieve(string $projectId): GetReportForwardingResponse
    {
        return $this->transport->object(GetReportForwardingResponse::class, 'GET /projects/{projectId}/report-forwarding', 'projects.reportForwarding.retrieve', ['projectId' => $projectId]);
    }

    /**
     * @param  ReportForwardingRequest  $body
     */
    public function update(string $projectId, array $body): UpdateReportForwardingResponse
    {
        return $this->transport->object(UpdateReportForwardingResponse::class, 'PUT /projects/{projectId}/report-forwarding', 'projects.reportForwarding.update', ['projectId' => $projectId], json: $body);
    }

    /**
     * Disables report forwarding. The API answers with HTTP 204 and no body.
     */
    public function delete(string $projectId): void
    {
        $this->transport->none('DELETE /projects/{projectId}/report-forwarding', 'projects.reportForwarding.delete', ['projectId' => $projectId]);
    }

    /**
     * @param  VerifyReportForwardingRequest  $body
     */
    public function verify(string $projectId, array $body): VerifyReportForwardingResponse
    {
        return $this->transport->object(VerifyReportForwardingResponse::class, 'POST /projects/{projectId}/report-forwarding/verify', 'projects.reportForwarding.verify', ['projectId' => $projectId], json: $body);
    }

    public function resendCode(string $projectId): ResendReportForwardingCodeResponse
    {
        return $this->transport->object(ResendReportForwardingCodeResponse::class, 'POST /projects/{projectId}/report-forwarding/resend-code', 'projects.reportForwarding.resendCode', ['projectId' => $projectId]);
    }
}
