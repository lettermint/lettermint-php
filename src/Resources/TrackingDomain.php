<?php

declare(strict_types=1);

namespace Lettermint\Resources;

use Lettermint\Types\ProjectTrackingDomain;
use Lettermint\Types\ProjectTrackingDomainMutationResponse;
use Lettermint\Types\ProjectTrackingDomainVerificationResponse;

/**
 * The custom tracking domain of a project: the domain that open and click
 * tracking links use instead of the Lettermint tracking domain. Needs the
 * team token.
 *
 * @phpstan-import-type UpdateProjectTrackingDomainData from \Lettermint\Types\ApiTypes
 */
final class TrackingDomain extends Resource
{
    /**
     * The project's custom tracking domain and whether its CNAME record is
     * verified. `domain` is null when the project uses the Lettermint
     * tracking domain.
     */
    public function retrieve(string $projectId): ProjectTrackingDomain
    {
        return $this->transport->object(ProjectTrackingDomain::class, 'GET /projects/{projectId}/tracking-domain', 'projects.trackingDomain.retrieve', ['projectId' => $projectId]);
    }

    /**
     * Sets or replaces the custom tracking domain. Add a CNAME record for the
     * domain that points to `cname_target`; Lettermint checks it in the
     * background, or check it now with verify(). Tracking links keep using the
     * Lettermint tracking domain until the domain is verified. Needs a plan
     * with custom tracking domains.
     *
     * @param  UpdateProjectTrackingDomainData  $body
     */
    public function update(string $projectId, array $body): ProjectTrackingDomainMutationResponse
    {
        return $this->transport->object(ProjectTrackingDomainMutationResponse::class, 'PUT /projects/{projectId}/tracking-domain', 'projects.trackingDomain.update', ['projectId' => $projectId], json: $body);
    }

    /**
     * Removes the custom tracking domain, so tracking links use the Lettermint
     * tracking domain again. The API answers with HTTP 204 and no body.
     */
    public function delete(string $projectId): void
    {
        $this->transport->none('DELETE /projects/{projectId}/tracking-domain', 'projects.trackingDomain.delete', ['projectId' => $projectId]);
    }

    /**
     * Checks the CNAME record of the custom tracking domain now. When the
     * record is missing or points to another target, when the DNS lookup
     * failed, or when the project has no custom tracking domain, the API
     * answers with HTTP 422 and this throws a ValidationException. Its `body`
     * then usually has the same shape as the response
     * (`ProjectTrackingDomainVerificationResponse::from($e->body)`), with
     * `data->temporary_error` true when the lookup failed and you should try
     * again later.
     */
    public function verify(string $projectId): ProjectTrackingDomainVerificationResponse
    {
        return $this->transport->object(ProjectTrackingDomainVerificationResponse::class, 'POST /projects/{projectId}/tracking-domain/verify', 'projects.trackingDomain.verify', ['projectId' => $projectId]);
    }
}
