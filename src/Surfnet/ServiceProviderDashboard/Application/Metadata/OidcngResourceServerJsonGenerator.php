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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\OidcGrantType;

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

    public function generateForNewEntity(ManageEntity $entity, string $workflowState): array
    {
        return [
            'data' => $this->generateDataForNewEntity($entity, $workflowState),
            'type' => 'oidc10_rp',
        ];
    }

    public function generateForExistingEntity(ManageEntity $entity, string $workflowState): array
    {
        return [
            'pathUpdates' => $this->generateDataForExistingEntity($entity, $workflowState),
            'type' => 'oidc10_rp',
            'id' => $entity->getId(),
        ];
    }

    private function generateDataForNewEntity(ManageEntity $entity, $workflowState)
    {
        // the type for entities is always oidc10-rp because manage is using saml internally
        $metadata = [
            'type' => 'oidc10-rp',
            'entityid' => OidcngClientIdParser::parse($entity->getMetaData()->getEntityId()),
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

    private function generateDataForExistingEntity(ManageEntity $entity, $workflowState)
    {
        $metadata = [
            'entityid' => OidcngClientIdParser::parse($entity->getMetaData()->getEntityId()),
            'state' => $workflowState,
        ];

        $metadata += $this->generateAclData($entity);

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
    private function generateMetadataFields(ManageEntity $entity)
    {
        $metadata = array_merge(
            [
                'description:en' => $entity->getMetaData()->getDescriptionEn(),
                'description:nl' => $entity->getMetaData()->getDescriptionNl(),
                'name:en' => $entity->getMetaData()->getNameEn(),
                'name:nl' => $entity->getMetaData()->getNameNl(),
            ],
            $this->generateAllContactsMetadata($entity),
            $this->generateOrganizationMetadata($entity),
            $this->privacyQuestionsMetadataGenerator->build($entity),
            $this->spDashboardMetadataGenerator->build($entity)
        );

        $service = $entity->getService();
        if ($service->getInstitutionId() != '') {
            $metadata['coin:institution_id'] = $service->getInstitutionId();
        }
        if ($service->getGuid() != '') {
            $metadata['coin:institution_guid'] = $service->getGuid();
        }

        // By default, OIDC TNG Resource servers are configured to have persistent subject type (name id format)
        $metadata['NameIDFormat'] = Constants::NAME_ID_FORMAT_PERSISTENT;

        // Will become configurable some time in the future.
        $metadata['scopes'] = ['openid'];

        $this->setExcludeFromPush($metadata, $entity);

        $metadata += $this->generateOidcClient($entity);

        return $metadata;
    }

    /**
     * @param ManageEntity $entity
     * @return array
     */
    private function generateOidcClient(ManageEntity $entity)
    {
        $metadata = [];
        if ($entity->getOidcClient()) {
            $secret = $entity->getOidcClient()->getClientSecret();
            if ($secret) {
                $metadata['secret'] = $secret;
            }
            // Reset the redirect URI list in order to get a correct JSON formatting (See #163646662)
            $metadata['grants'] = [OidcGrantType::GRANT_TYPE_CLIENT_CREDENTIALS];
            $metadata['isResourceServer'] = true;
        }
        return $metadata;
    }

    private function generateAllContactsMetadata(ManageEntity $entity): array
    {
        $metadata = [];
        $index = 0;

        $contacts = $entity->getMetaData()->getContacts();

        if ($contacts) {
            if ($contacts->findSupportContact()) {
                $metadata += $this->generateContactMetadata(
                    'support',
                    $index++,
                    $contacts->findSupportContact()
                );
            }

            if ($contacts->findAdministrativeContact()) {
                $metadata += $this->generateContactMetadata(
                    'administrative',
                    $index++,
                    $contacts->findAdministrativeContact()
                );
            }

            if ($contacts->findTechnicalContact()) {
                $metadata += $this->generateContactMetadata(
                    'technical',
                    $index++,
                    $contacts->findTechnicalContact()
                );
            }
        }
        return $metadata;
    }

    private function generateOrganizationMetadata(ManageEntity $entity): array
    {
        $metadata = [];
        $organization = $entity->getMetaData()->getOrganization();
        if ($organization) {
            $metadata = [
                'OrganizationName:en' => $organization->getNameEn(),
                'OrganizationDisplayName:en' => $organization->getDisplayNameEn(),
                'OrganizationURL:en' => $organization->getUrlEn(),
                'OrganizationName:nl' => $organization->getNameNl(),
                'OrganizationDisplayName:nl' => $organization->getDisplayNameNl(),
                'OrganizationURL:nl' => $organization->getUrlNl(),
            ];
        }
        return array_filter($metadata);
    }

    private function generateContactMetadata($contactType, $index, Contact $contact): array
    {
        $metadata = [
            sprintf('contacts:%d:contactType', $index) => $contactType,
        ];

        if (!empty($contact->getGivenName())) {
            $metadata[sprintf('contacts:%d:givenName', $index)] = $contact->getGivenName();
        }

        if (!empty($contact->getSurName())) {
            $metadata[sprintf('contacts:%d:surName', $index)] = $contact->getSurName();
        }

        if (!empty($contact->getEmail())) {
            $metadata[sprintf('contacts:%d:emailAddress', $index)] = $contact->getEmail();
        }

        if (!empty($contact->getPhone())) {
            $metadata[sprintf('contacts:%d:telephoneNumber', $index)] = $contact->getPhone();
        }

        return $metadata;
    }

    private function generateAclData(ManageEntity $entity): array
    {
        $acl = $entity->getAllowedIdentityProviders();
        $providers = [];
        if ($acl){
            if ($acl->isAllowAll()) {
                return [
                    'allowedEntities' => [],
                    'allowedall' => true,
                ];
            }


            foreach ($acl->getAllowedIdentityProviders() as $entityId) {
                $providers[] = [
                    'name' => $entityId,
                ];
            }
        }
        return [
            'allowedEntities' => $providers,
            'allowedall' => false,
        ];
    }

    private function setExcludeFromPush(&$metadata, ManageEntity $entity): void
    {
        // Scenario 1: When publishing to production, the coin:exclude_from_push must be present and set to '1'.
        // This prevents the entity from being pushed to EngineBlock.
        if ($entity->isProduction()) {
            $metadata['coin:exclude_from_push'] = '1';
        }

        // Scenario 2: When dealing with a client secret reset, keep the current exclude from push state.
        $secret = $entity->getOidcClient()->getClientSecret();
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
