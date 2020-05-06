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

use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\PrivacyQuestionsMetadataGenerator;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\SpDashboardMetadataGenerator;
use Surfnet\ServiceProviderDashboard\Application\Parser\OidcngClientIdParser;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\OidcGrantType;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto\ManageEntity;

/**
 * The OidcngResourceServerJsonGenerator generates oidc10_rp resource server entity json
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ElseExpression)
 */
class OidcngResourceServerJsonGenerator implements GeneratorInterface
{
    /**
     * @var PrivacyQuestionsMetadataGenerator
     */
    private $privacyQuestionsMetadataGenerator;

    /**
     * @var SpDashboardMetadataGenerator
     */
    private $spDashboardMetadataGenerator;

    /**
     * @param PrivacyQuestionsMetadataGenerator $privacyQuestionsMetadataGenerator
     * @param SpDashboardMetadataGenerator $spDashboardMetadataGenerator
     */
    public function __construct(
        PrivacyQuestionsMetadataGenerator $privacyQuestionsMetadataGenerator,
        SpDashboardMetadataGenerator $spDashboardMetadataGenerator
    ) {
        $this->privacyQuestionsMetadataGenerator = $privacyQuestionsMetadataGenerator;
        $this->spDashboardMetadataGenerator = $spDashboardMetadataGenerator;
    }

    /**
     * @param Entity $entity
     * @param string $workflowState
     * @return array
     */
    public function generateForNewEntity(Entity $entity, $workflowState)
    {
        return [
            'data' => $this->generateDataForNewEntity($entity, $workflowState),
            'type' => 'oidc10_rp',
        ];
    }

    /**
     * @param Entity $entity
     * @param ManageEntity $manageEntity
     * @param string $workflowState
     * @return array
     */
    public function generateForExistingEntity(Entity $entity, ManageEntity $manageEntity, $workflowState)
    {
        $data = [
            'pathUpdates' => $this->generateDataForExistingEntity($entity, $manageEntity, $workflowState),
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
    private function generateDataForNewEntity(Entity $entity, $workflowState)
    {
        // the type for entities is always oidc10-rp because manage is using saml internally
        $metadata = [
            'type' => 'oidc10-rp',
            'entityid' => OidcngClientIdParser::parse($entity->getEntityId()),
            'active' => true,
            'state' => $workflowState,
            'metaDataFields' => $this->generateMetadataFields($entity),
        ];

        $metadata += $this->generateAclData($entity);

        if ($entity->hasComments()) {
            $metadata['revisionnote'] = $entity->getComments();
        }

        return $metadata;
    }

    /**
     * @param Entity $entity
     * @param string $workflowState
     * @return array
     */
    private function generateDataForExistingEntity(Entity $entity, ManageEntity $manageEntity, $workflowState)
    {
        $metadata = [
            'entityid' => OidcngClientIdParser::parse($entity->getEntityId()),
            'state' => $workflowState,
        ];

        $metadata += $this->generateAclData($entity);

        $metadata += $this->flattenMetadataFields(
            $this->generateMetadataFields($entity, $manageEntity)
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
    private function generateMetadataFields(Entity $entity, ManageEntity $manageEntity = null)
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

        // Will become configurable some time in the future.
        $metadata['scopes'] = ['openid'];

        // When publishing to production, the coin:exclude_from_push must be present and set to '1'. This prevents the
        // entity from being pushed to engineblock.
        if ($entity->isProduction()) {
            $metadata['coin:exclude_from_push'] = '1';
        }

        $metadata += $this->generateOidcClient($entity);

        return $metadata;
    }

    /**
     * @param Entity $entity
     * @return array
     */
    private function generateOidcClient(Entity $entity)
    {
        $metadata = [];
        $secret = $entity->getClientSecret();
        if ($secret) {
            $metadata['secret'] = $secret;
        }
        // Reset the redirect URI list in order to get a correct JSON formatting (See #163646662)
        $metadata['grants'] = [OidcGrantType::GRANT_TYPE_CLIENT_CREDENTIALS];
        $metadata['isResourceServer'] = true;

        return $metadata;
    }

    /**
     * @param Entity $entity
     * @return array
     */
    private function generateAllContactsMetadata(Entity $entity)
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
     * @param Entity $entity
     * @return array
     */
    private function generateOrganizationMetadata(Entity $entity)
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
     * @param Entity $entity
     * @return array
     */
    private function generateAclData(Entity $entity)
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
}
