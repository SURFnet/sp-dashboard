<?php

/**
 * Copyright 2017 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Surfnet\ServiceProviderDashboard\Webtests\Manage\Client;

use RuntimeException;
use Surfnet\ServiceProviderDashboard\Domain\Repository\QueryManageRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\DataFixtures\ORM\WebTestFixtures;
use function file_get_contents;
use function json_decode;
use function sprintf;
use function str_replace;

class ClientResult implements ClientResultInterface
{
    private $id;

    private $protocol;

    private $entityId;

    private $metadataUrl;

    private $name;

    private $teamName;

    private string $institutionId;
    private string $nameIdFormat;

    public function __construct(
        string $protocol,
        string $id,
        string $entityId,
        ?string $metadataUrl,
        string $name,
        ?string $teamName,
        ?string $institutionId,
        ?string $nameIdFormat,
    ) {
        $this->id = $id;
        $this->protocol = $protocol;
        $this->entityId = $entityId;
        if ($metadataUrl === null) {
            $metadataUrl = $entityId . '/metadata';
        }
        $this->metadataUrl = $metadataUrl;
        $this->name = $name;
        $this->teamName = $teamName;
        $this->institutionId = $institutionId;
        if ($teamName === null) {
            $this->teamName = WebTestFixtures::TEAMNAME_SURF;
        }
        $this->nameIdFormat = $nameIdFormat ?? 'nameidformat';
    }

    public function getEntityResult(): array
    {
        switch ($this->protocol) {
            case "saml20_sp":
                $json = file_get_contents(__DIR__ . '/template/saml20_sp.json');
                break;
            case "saml20_idp":
                $json = file_get_contents(__DIR__ . '/template/saml20_idp.json');
                break;
            case "oidc10_rp":
                $json = file_get_contents(__DIR__ . '/template/oidc10.json');
                break;
            case "oauth20_ccc":
                $json = file_get_contents(__DIR__ . '/template/ccc.json');
                break;
            default:
                throw new RuntimeException(sprintf("Protocol %s is not supported", $this->protocol));
        }
        $data = sprintf(
            $json,
            $this->id,
            $this->protocol,
            $this->entityId,
            $this->metadataUrl,
            $this->name,
            str_replace('_', '-', $this->protocol),
            $this->teamName,
            $this->institutionId,
            $this->nameIdFormat,
        );
        return json_decode($data, true);
    }

    public function getSearchResult(): array
    {
        $json = file_get_contents(__DIR__ . '/fixture/search.json');
        return json_decode(sprintf($json, $this->id, $this->protocol, $this->entityId, $this->name), true);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEntityId(): string
    {
        return $this->entityId;
    }

    public static function decode($data): self
    {
        return new self(
            $data['protocol'],
            $data['id'],
            $data['entityId'],
            $data['metadataUrl'],
            $data['name'],
            $data['teamName'],
            $data['institutionId'],
            $data['nameIdFormat'],
        );
    }

    public function encode(): array
    {
        return [
            'id' => $this->id,
            'protocol' => $this->protocol,
            'entityId' => $this->entityId,
            'metadataUrl' => $this->metadataUrl,
            'name' => $this->name,
            'teamName' => $this->teamName,
            'institutionId' => $this->institutionId,
            'nameIdFormat' => $this->nameIdFormat,
        ];
    }
}
