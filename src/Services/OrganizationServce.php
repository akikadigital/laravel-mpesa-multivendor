<?php

namespace Akika\LaravelMpesaMultivendor\Services;

use Akika\LaravelMpesaMultivendor\Support\MpesaClient;

class OrganizationServce
{
    public function __construct(
        protected MpesaClient $client
    ) {}

    /**
     * Get organization details using the shortcode.
     *
     * @param string $shortcode The shortcode of the organization to query.
     * @return array The response from the API containing organization details.
     */
    public function getOrganizationDetails(
        string $shortcode
    ): array
    {
        $url = $this->client->baseUrl() . '/sfcverify/v1/query/info';
        $data = [
            'IdentifierType' => 4,
            'Identifier' => $shortcode,
        ];

        $result = $this->client->makeRequest($url, $data);

        if ($this->client->isDebugMode()) {
            info('Organization Details Response Data', $result);
        }

        return $result;
    }
}