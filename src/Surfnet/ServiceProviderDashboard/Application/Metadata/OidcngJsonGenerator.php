<?php

/**
 * Copyright 2019 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Application\Metadata;

use Surfnet\ServiceProviderDashboard\Application\Dto\MetadataConversionDto;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\ArpGenerator;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\PrivacyQuestionsMetadataGenerator;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\SpDashboardMetadataGenerator;
use Surfnet\ServiceProviderDashboard\Application\Parser\OidcngClientIdParser;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto\ManageEntity;

/**
 * The OidcngJsonGenerator generates oidc10_rp entity json
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ElseExpression)
 */
class OidcngJsonGenerator implements GeneratorInterface
{
    /**
     * @var ArpGenerator
     */
    private $arpMetadataGenerator;

    /**
     * @var PrivacyQuestionsMetadataGenerator
     */
    private $privacyQuestionsMetadataGenerator;

    /**
     * @var SpDashboardMetadataGenerator
     */
    private $spDashboardMetadataGenerator;

    /**
     * @var string
     */
    private $oidcPlaygroundUriTest;

    /**
     * @var string
     */
    private $oidcPlaygroundUriProd;

    /**
     * @param ArpGenerator $arpMetadataGenerator
     * @param PrivacyQuestionsMetadataGenerator $privacyQuestionsMetadataGenerator
     * @param SpDashboardMetadataGenerator $spDashboardMetadataGenerator
     * @param string $oidcPlaygroundUriTest
     * @param string $oidcPlaygroundUriProd
     */
    public function __construct(
        ArpGenerator $arpMetadataGenerator,
        PrivacyQuestionsMetadataGenerator $privacyQuestionsMetadataGenerator,
        SpDashboardMetadataGenerator $spDashboardMetadataGenerator,
        $oidcPlaygroundUriTest,
        $oidcPlaygroundUriProd
    ) {
        $this->arpMetadataGenerator = $arpMetadataGenerator;
        $this->privacyQuestionsMetadataGenerator = $privacyQuestionsMetadataGenerator;
        $this->spDashboardMetadataGenerator = $spDashboardMetadataGenerator;
        $this->oidcPlaygroundUriTest = $oidcPlaygroundUriTest;
        $this->oidcPlaygroundUriProd = $oidcPlaygroundUriProd;
    }

    /**
     * @param MetadataConversionDto $entity
     * @param string $workflowState
     * @return array
     */
    public function generateForNewEntity(MetadataConversionDto $entity, $workflowState)
    {
        // the type for entities is always saml because manage is using saml internally
        return [
            'data' => $this->generateDataForNewEntity($entity, $workflowState),
            'type' => 'oidc10_rp',
        ];
    }

    /**
     * @param MetadataConversionDto $entity
     * @param string $workflowState
     * @return array
     */
    public function generateForExistingEntity(MetadataConversionDto $entity, $workflowState)
    {
        $data = [
            'pathUpdates' => $this->generateDataForExistingEntity($entity, $workflowState),
            'type' => 'oidc10_rp',
            'id' => $entity->getManageId(),
        ];

        return $data;
    }

    /**
     * @param Entity $entity
     * @param string $workflowState
     * @return array
     */
    private function generateDataForNewEntity(MetadataConversionDto $entity, $workflowState)
    {
        // the type for entities is always oidc10-rp because manage is using saml internally
        $metadata = [
            'arp' => $this->arpMetadataGenerator->build($entity),
            'type' => 'oidc10-rp',
            'entityid' => OidcngClientIdParser::parse($entity->getEntityId()),
            'active' => true,
            'state' => $workflowState,
            'metaDataFields' => $this->generateMetadataFields($entity),
        ];

        $metadata += $this->generateAclData($entity);
        $metadata += $this->generateAllowedResourceServers($entity);

        if ($entity->hasComments()) {
            $metadata['revisionnote'] = $entity->getComments();
        }

        return $metadata;
    }

    /**
     * @param MetadataConversionDto $entity
     * @param string $workflowState
     * @return array
     */
    private function generateDataForExistingEntity(MetadataConversionDto $entity, $workflowState)
    {
        $metadata = [
            'arp' => $this->arpMetadataGenerator->build($entity),
            'entityid' => OidcngClientIdParser::parse($entity->getEntityId()),
            'state' => $workflowState,
        ];

        $metadata += $this->generateAclData($entity);
        $metadata += $this->generateAllowedResourceServers($entity);

        $metadata += $this->flattenMetadataFields(
            $this->generateMetadataFields($entity)
        );

        if ($entity->hasComments()) {
            $metadata['revisionnote'] = $entity->getComments();
        }

        return $metadata;
    }

    /**
     * Convert a list fields to a flat array expected by the merge-write API.
     *
     * Manage always returns metadata fields like this:
     *
     *     [ metaDataFields => [ description:en => ..., description:nl => ..., ...] ]
     *
     * But when using the merge-write API, sending a 'metaDataFields' property
     * will overwrite all existing metadata fields. To prevent this, we only
     * send the metadata fields we actually want to update by using the flat
     * format:
     *
     *     [ metaDataFields.description:en => ..., metaDataFields.description:nl => ..., ...] ]
     *
     *
     * @param array $fields
     * @return array
     */
    private function flattenMetadataFields(array $fields)
    {
        $flatFields = [];

        foreach ($fields as $name => $value) {
            $flatFields['metaDataFields.'.$name] = $value;
        }

        return $flatFields;
    }

    /**
     * @param Entity $entity
     * @return array
     */
    private function generateMetadataFields(MetadataConversionDto $entity)
    {
        $metadata = array_merge(
            [
                'description:en' => $entity->getDescriptionEn(),
                'description:nl' => $entity->getDescriptionNl(),
                'name:en' => $entity->getNameEn(),
                'name:nl' => $entity->getNameNl(),
            ],
            $this->generateAllContactsMetadata($entity),
            $this->generateOrganizationMetadata($entity),
            $this->privacyQuestionsMetadataGenerator->build($entity),
            $this->spDashboardMetadataGenerator->build($entity)
        );

        $metadata['NameIDFormat'] = $entity->getNameIdFormat();

        // If the entity exists in Manage, use the scopes configured there.
        if ($entity->isManageEntity()) {
            // This prevents overwriting the scopes attribute. See: https://www.pivotaltracker.com/story/show/170868465
            $metadata['scopes'] = $entity->getScopes();
        } else {
            // Will become configurable some time in the future.
            $metadata['scopes'] = ['openid'];
        }

        $this->setExcludeFromPush($metadata, $entity);

        $metadata += $this->generateOidcClient($entity);

        if (!empty($entity->getLogoUrl())) {
            $metadata += $this->generateLogoMetadata($entity);
        }

        if ($entity->isEnablePlayground()) {
            if ($entity->isProduction()) {
                $metadata['redirectUrls'][] = $this->oidcPlaygroundUriProd;
            } else {
                $metadata['redirectUrls'][] = $this->oidcPlaygroundUriTest;
            }
        }

        return $metadata;
    }

    /**
     * @param MetadataConversionDto $entity
     * @return array
     */
    private function generateOidcClient(MetadataConversionDto $entity)
    {
        $metadata = [];
        $secret = $entity->getClientSecret();
        if ($secret) {
            $metadata['secret'] = $secret;
        }
        // Reset the redirect URI list in order to get a correct JSON formatting (See #163646662)
        $metadata['redirectUrls'] = $entity->getRedirectUris();
        $metadata['grants'] = [$entity->getGrantType()->getGrantType()];
        $metadata['accessTokenValidity'] = $entity->getAccessTokenValidity();
        $metadata['isPublicClient'] = $entity->isPublicClient();

        return $metadata;
    }

    /**
     * @param MetadataConversionDto $entity
     * @return array
     */
    private function generateAllContactsMetadata(MetadataConversionDto $entity)
    {
        $metadata = [];
        $index = 0;

        if ($entity->getSupportContact()) {
            $metadata += $this->generateContactMetadata('support', $index++, $entity->getSupportContact());
        }

        if ($entity->getAdministrativeContact()) {
            $metadata += $this->generateContactMetadata(
                'administrative',
                $index++,
                $entity->getAdministrativeContact()
            );
        }

        if ($entity->getTechnicalContact()) {
            $metadata += $this->generateContactMetadata('technical', $index++, $entity->getTechnicalContact());
        }

        return $metadata;
    }

    /**
     * @param MetadataConversionDto $entity
     * @return array
     */
    private function generateOrganizationMetadata(MetadataConversionDto $entity)
    {
        $metadata = [
            'OrganizationName:en' => $entity->getOrganizationNameEn(),
            'OrganizationDisplayName:en' => $entity->getOrganizationDisplayNameEn(),
            'OrganizationURL:en' => $entity->getOrganizationUrlEn(),
            'OrganizationName:nl' => $entity->getOrganizationNameNl(),
            'OrganizationDisplayName:nl' => $entity->getOrganizationDisplayNameNl(),
            'OrganizationURL:nl' => $entity->getOrganizationUrlNl(),
        ];

        return array_filter($metadata);
    }

    /**
     * @param string $contactType
     * @param int $index
     * @param Contact $contact
     * @return array
     */
    private function generateContactMetadata($contactType, $index, Contact $contact)
    {
        $metadata = [
            sprintf('contacts:%d:contactType', $index) => $contactType,
        ];

        if (!empty($contact->getFirstName())) {
            $metadata[sprintf('contacts:%d:givenName', $index)] = $contact->getFirstName();
        }

        if (!empty($contact->getLastName())) {
            $metadata[sprintf('contacts:%d:surName', $index)] = $contact->getLastName();
        }

        if (!empty($contact->getEmail())) {
            $metadata[sprintf('contacts:%d:emailAddress', $index)] = $contact->getEmail();
        }

        if (!empty($contact->getPhone())) {
            $metadata[sprintf('contacts:%d:telephoneNumber', $index)] = $contact->getPhone();
        }

        return $metadata;
    }

    /**
     * Generate logo metadata fields.
     *
     * Logo dimensions are required in the SAML spec. They are always present,
     * except when the user just created the entity in the interface. We
     * determine the dimensions in those situations.
     *
     * @param MetadataConversionDto $entity
     * @return array
     */
    private function generateLogoMetadata(MetadataConversionDto $entity)
    {
        $metadata = [
            'logo:0:url' => $entity->getLogoUrl(),
        ];

        $logoData = @getimagesize(
            $entity->getLogoUrl()
        );

        if ($logoData !== false) {
            list($width, $height) = $logoData;
        } else {
            $width = 50;
            $height = 50;
        }

        $metadata['logo:0:width'] = (string)$width;
        $metadata['logo:0:height'] = (string)$height;

        return $metadata;
    }

    /**
     * @param MetadataConversionDto $entity
     * @return array
     */
    private function generateAclData(MetadataConversionDto $entity)
    {
        if ($entity->isIdpAllowAll()) {
            return [
                'allowedEntities' => [],
                'allowedall' => true,
            ];
        }

        $providers = [];
        foreach ($entity->getIdpWhitelist() as $entityId) {
            $providers[] = [
                'name' => $entityId,
            ];
        }

        return [
            'allowedEntities' => $providers,
            'allowedall' => false,
        ];
    }

    private function generateAllowedResourceServers(MetadataConversionDto $entity)
    {
        $collection = $entity->getOidcngResourceServers();
        $allowedResourceServers = [];

        if ($collection) {
            $servers = $collection->getResourceServers();
            foreach ($servers as $clientId) {
                $allowedResourceServers[]['name'] = $clientId;
            }
        }

        return [
            'allowedResourceServers' => $allowedResourceServers,
        ];
    }

    private function setExcludeFromPush(&$metadata, MetadataConversionDto $entity)
    {
        // Scenario 1: When publishing to production, the coin:exclude_from_push must be present and set to '1'.
        // This prevents the entity from being pushed to EngineBlock.
        if ($entity->isProduction()) {
            $metadata['coin:exclude_from_push'] = '1';
        }

        // Scenario 2: When dealing with a client secret reset, keep the current exclude from push state.
        $secret = $entity->getClientSecret();
        if ($secret && $entity->isManageEntity() && !$entity->isExcludedFromPush()) {
            $metadata['coin:exclude_from_push'] = '0';
        }

        // Scenario 3: We are resetting the client secret, the service desk removed the exclude from push coin
        // attribute. This also indicates the entity is published. But now we do not want to reset the coin to '0', we
        // simply unset it.
        if ($secret && $entity->isManageEntity() && !$entity->isExcludedFromPushSet()) {
            unset($metadata['coin:exclude_from_push']);
        }
    }
}
