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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact as ContactEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\EntityDiff;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use function sprintf;

/**
 * The OidcngResourceServerJsonGenerator generates oauth20-rs resource server entity json
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
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
            'type' => 'oauth20_rs',
        ];
    }

    public function generateForExistingEntity(
        ManageEntity $entity,
        EntityDiff $differences,
        string $workflowState,
        string $updatedPart = ''
    ): array {
        return [
            'pathUpdates' => $this->generateDataForExistingEntity($entity, $differences, $workflowState),
            'type' => 'oauth20_rs',
            'active' => true,
            'id' => $entity->getId(),
        ];
    }

    public function generateEntityChangeRequest(
        ManageEntity $entity,
        EntityDiff $differences,
        ContactEntity $contact
    ): array {
        $payload = [
            'metaDataId' => $entity->getId(),
            'type' => 'oauth20_rs',
            'pathUpdates' => $this->generateForChangeRequest($differences),
            'auditData' => [
                'user' => $contact->getEmailAddress()
            ],
        ];

        $payload['note'] = $entity->getRevisionNote();

        return $payload;
    }

    private function generateDataForNewEntity(ManageEntity $entity, $workflowState)
    {
        // the type for entities is always oidc10-rp because manage is using saml internally
        $metadata = [
            'type' => 'oauth20-rs',
            'entityid' => OidcngClientIdParser::parse($entity->getMetaData()->getEntityId()),
            'active' => true,
            'state' => $workflowState,
            'metaDataFields' => $this->generateMetadataFields($entity),
        ];

        $metadata['revisionnote'] = $entity->getRevisionNote();

        return $metadata;
    }

    private function generateDataForExistingEntity(
        ManageEntity $entity,
        EntityDiff $differences,
        string $workflowState
    ): array {
        $metadata = [
            'entityid' => OidcngClientIdParser::parse($entity->getMetaData()->getEntityId()),
            'state' => $workflowState,
        ];

        $metadata += $differences->getDiff();
        $this->setExcludeFromPush($metadata, $entity, true);
        $metadata['revisionnote'] = $entity->getRevisionNote();

        return $metadata;
    }

    private function generateMetadataFields(ManageEntity $entity): array
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
        if ($service->getInstitutionId() && $service->getInstitutionId() != '') {
            $metadata['coin:institution_id'] = $service->getInstitutionId();
        }
        if ($service->getGuid() != '') {
            $metadata['coin:institution_guid'] = $service->getGuid();
        }

        // By default, OIDC TNG Resource servers are configured to have persistent subject type (name id format)
        $metadata['NameIDFormat'] = Constants::NAME_ID_FORMAT_PERSISTENT;

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
                'OrganizationName:nl' => $organization->getNameNl(),
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

    private function setExcludeFromPush(&$metadata, ManageEntity $entity, $flatten = false): void
    {
        $fieldName = 'coin:exclude_from_push';
        if ($flatten) {
            $fieldName = sprintf('metaDataFields.coin:exclude_from_push');
        }

        // Scenario 1: When publishing to production, the coin:exclude_from_push must be present and set to '1'.
        // This prevents the entity from being pushed to EngineBlock.
        if ($entity->isProduction()) {
            $metadata[$fieldName] = '1';
        }

        // Scenario 2: When dealing with a client secret reset, keep the current exclude from push state.
        $secret = $entity->getOidcClient()->getClientSecret();
        if ($secret && $entity->isManageEntity() && !$entity->isExcludedFromPush()) {
            $metadata[$fieldName] = '0';
        }

        // Scenario 3: We are resetting the client secret, the service desk removed the exclude from push coin
        // attribute. This also indicates the entity is published. But now we do not want to reset the coin to '0', we
        // simply unset it.
        if ($secret && $entity->isManageEntity() && !$entity->isExcludedFromPushSet()) {
            unset($metadata[$fieldName]);
        }
    }

    private function generateForChangeRequest(EntityDiff $differences): array
    {
        return $differences->getDiff();
    }
}
