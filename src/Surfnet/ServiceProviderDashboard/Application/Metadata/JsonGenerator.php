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

namespace Surfnet\ServiceProviderDashboard\Application\Metadata;

use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\ArpGenerator;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\PrivacyQuestionsMetadataGenerator;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\SpDashboardMetadataGenerator;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact as ContactEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\EntityDiff;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Logo;

/**
 * The JsonGenerator is able to generate Manage SAML 2.0 JSON metadata
 *
 * @SuppressWarnings(PHPMD)
 */
class JsonGenerator implements GeneratorInterface
{
    public function __construct(
        private readonly ArpGenerator $arpMetadataGenerator,
        private readonly PrivacyQuestionsMetadataGenerator $privacyQuestionsMetadataGenerator,
        private readonly SpDashboardMetadataGenerator $spDashboardMetadataGenerator,
    ) {
    }

    public function generateForNewEntity(ManageEntity $entity, string $workflowState): array
    {
        // the type for entities is always saml because manage is using saml internally
        return [
            'data' => $this->generateDataForNewEntity($entity, $workflowState),
            'type' => 'saml20_sp',
        ];
    }

    public function generateForExistingEntity(
        ManageEntity $entity,
        EntityDiff $differences,
        string $workflowState,
        string $updatedPart = '',
    ): array {
        // the type for entities is always saml because manage is using saml internally
        $data = [
            'pathUpdates' => $this->generateDataForExistingEntity($entity, $differences, $workflowState, $updatedPart),
            'type' => 'saml20_sp',
            'id' => $entity->getId(),
        ];

        return $data;
    }

    public function generateEntityChangeRequest(
        ManageEntity $entity,
        EntityDiff $differences,
        ContactEntity $contact,
    ): array {
        $payload = [
            'metaDataId' => $entity->getId(),
            'type' => 'saml20_sp',
            'pathUpdates' => $this->generateForChangeRequest($entity, $differences),
            'auditData' => [
                'user' => $contact->getEmailAddress(),
            ],
        ];

        $payload['note'] = $entity->getRevisionNote();
        return $payload;
    }

    private function generateDataForNewEntity(ManageEntity $entity, string $workflowState): array
    {
        // the type for entities is always saml because manage is using saml internally
        $metadata = [
            'arp' => $this->arpMetadataGenerator->build($entity),
            'type' => 'saml20-sp',
            'entityid' => $entity->getMetaData()->getEntityId(),
            'active' => true,
            'state' => $workflowState,
            'metaDataFields' => $this->generateMetadataFields($entity),
        ];

        $metadata += $this->generateAclData($entity);
        $metadata['metadataurl'] = $entity->getMetaData()->getMetadataUrl();

        $metadata['revisionnote'] = $entity->getRevisionNote();

        return $metadata;
    }

    private function generateDataForExistingEntity(
        ManageEntity $entity,
        EntityDiff $differences,
        string $workflowState,
        string $updatedPart,
    ): array {
        $metadata = [
            'entityid' => $entity->getMetaData()->getEntityId(),
        ];
        switch ($updatedPart) {
            case 'ACL':
                return $metadata + $this->generateAclData($entity);

            default:
                $metadata += $differences->getDiff();
                // We generate empty acs locations to ensure we clean up all existing acs locations in manage
                // this way we don't end up with stray acs locations that should have been deleted.
                // See: MetaDataTest::test_it_adds_empty_acs_locations
                $this->generateAcsLocations($entity, $metadata, true);
                if ($entity->getProtocol()->getProtocol() === Constants::TYPE_SAML) {
                    $metadata['metadataurl'] = $entity->getMetaData()->getMetadataUrl();
                }
                $metadata = $this->generateArp($metadata, $entity);
                $metadata['state'] = $workflowState;
                $metadata['revisionnote'] = $entity->getRevisionNote();

                // When publishing to production, the coin:exclude_from_push must be present and set to '1'. This prevents the
                // entity from being pushed to EngineBlock. Once the entity is checked a final time, the flag is set to 0
                // by one of the administrators. If the entity was included for push, we make sure it is not overridden.
                if ($entity->isProduction()) {
                    $metadata['metaDataFields.coin:exclude_from_push'] = '1';
                }
                if ($entity->isManageEntity() && !$entity->isExcludedFromPush()) {
                    $metadata['metaDataFields.coin:exclude_from_push'] = '0';
                }
                $this->privacyQuestionsMetadataGenerator->withMetadataPrefix();

                return $metadata + $this->privacyQuestionsMetadataGenerator->build($entity);
        }
    }

    private function generateForChangeRequest(ManageEntity $entity, EntityDiff $differences): array
    {
        $metadata = $differences->getDiff();
        return $this->generateArp($metadata, $entity);
    }

    private function generateAcsLocations(ManageEntity $entity, array &$metadata, bool $addPrefix = false): void
    {
        AcsLocationHelper::addAcsLocationsToMetaData($entity->getMetaData()->getAcsLocations(), $metadata, $addPrefix);
        if ($entity->isManageEntity()) {
            AcsLocationHelper::addEmptyAcsLocationsToMetaData(
                $entity->getMetaData()->getAcsLocations(),
                $metadata,
                $addPrefix
            );
        }
    }

    /**
     * @return mixed[]
     */
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

        $this->generateAcsLocations($entity, $metadata);

        $metadata['NameIDFormat'] = $entity->getMetaData()->getNameIdFormat();
        $metadata['coin:signature_method'] = 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256';
        $metadata = array_merge($metadata, $this->generateCertDataMetadata($entity));

        // When publishing to production, the coin:exclude_from_push must be present and set to '1'. This prevents the
        // entity from being pushed to EngineBlock. Once the entity is checked a final time, the flag is set to 0
        // by one of the administrators. If the entity was included for push, we make sure it is not overridden.
        if ($entity->isProduction()) {
            $metadata['coin:exclude_from_push'] = '1';
        }
        if ($entity->isManageEntity() && !$entity->isExcludedFromPush()) {
            $metadata['coin:exclude_from_push'] = '0';
        }
        if ($entity->getMetaData()->getLogo() instanceof Logo && $entity->getMetaData()->getLogo()->getUrl() !== '') {
            $metadata = array_merge($metadata, $this->generateLogoMetadata($entity));
        }

        return $metadata;
    }

    private function generateCertDataMetadata(ManageEntity $entity): array
    {
        $metadata = [];
        if ($entity->getMetaData()->getCertData() !== null
            && $entity->getMetaData()->getCertData() !== ''
            && $entity->getMetaData()->getCertData() !== '0') {
            $metadata['certData'] = $this->stripCertificateEnvelope(
                $entity->getMetaData()->getCertData()
            );
        }

        return $metadata;
    }

    /**
     * Strip header and footer from certificate data.
     */
    private function stripCertificateEnvelope(string $certData): string
    {
        $certData = str_replace('-----BEGIN CERTIFICATE-----', '', $certData);
        $certData = str_replace('-----END CERTIFICATE-----', '', $certData);

        return trim($certData);
    }

    private function generateAllContactsMetadata(ManageEntity $entity): array
    {
        $metadata = [];
        $index = 0;

        if ($entity->getMetaData()->getContacts()->findSupportContact() !== null) {
            $metadata += $this->generateContactMetadata(
                'support',
                $index++,
                $entity->getMetaData()->getContacts()->findSupportContact()
            );
        }

        if ($entity->getMetaData()->getContacts()->findAdministrativeContact() !== null) {
            $metadata += $this->generateContactMetadata(
                'administrative',
                $index++,
                $entity->getMetaData()->getContacts()->findAdministrativeContact()
            );
        }

        if ($entity->getMetaData()->getContacts()->findTechnicalContact() !== null) {
            $metadata += $this->generateContactMetadata(
                'technical',
                $index++,
                $entity->getMetaData()->getContacts()->findTechnicalContact()
            );
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

    private function generateContactMetadata(string $contactType, int $index, Contact $contact): array
    {
        $metadata = [
            sprintf('contacts:%d:contactType', $index) => $contactType,
        ];

        if ($contact->getGivenName() !== null && $contact->getGivenName() !== '' && $contact->getGivenName() !== '0') {
            $metadata[sprintf('contacts:%d:givenName', $index)] = $contact->getGivenName();
        }

        if ($contact->getSurName() !== null && $contact->getSurName() !== '' && $contact->getSurName() !== '0') {
            $metadata[sprintf('contacts:%d:surName', $index)] = $contact->getSurName();
        }

        if ($contact->getEmail() !== null && $contact->getEmail() !== '' && $contact->getEmail() !== '0') {
            $metadata[sprintf('contacts:%d:emailAddress', $index)] = $contact->getEmail();
        }

        if ($contact->getPhone() !== null && $contact->getPhone() !== '' && $contact->getPhone() !== '0') {
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
     * @SuppressWarnings(PHPMD.ErrorControlOperator)
     */
    private function generateLogoMetadata(ManageEntity $entity): array
    {
        $logo = $entity->getMetaData()->getLogo();
        $metadata = [];
        if ($logo) {
            $metadata = [
                'logo:0:url' => $logo->getUrl(),
            ];

            $logoData = @getimagesize(
                $logo->getUrl()
            );

            if ($logoData !== false) {
                [$width, $height] = $logoData;
            } else {
                $width = 50;
                $height = 50;
            }

            $metadata['logo:0:width'] = (string)$width;
            $metadata['logo:0:height'] = (string)$height;
        }
        return $metadata;
    }

    private function generateAclData(ManageEntity $entity): array
    {
        if ($entity->getAllowedIdentityProviders()->isAllowAll()) {
            return [
                'allowedEntities' => [],
                'allowedall' => true,
            ];
        }

        $providers = [];
        foreach ($entity->getAllowedIdentityProviders()->getAllowedIdentityProviders() as $entityId) {
            $providers[] = [
                'name' => $entityId,
            ];
        }

        return [
            'allowedEntities' => $providers,
            'allowedall' => false,
        ];
    }

    private function generateArp(array $metadata, ManageEntity $entity): array
    {
        // Arp is to be sent in its entirety as it does not support the MERGE WRITE feature
        // but we use the diffed arp to check if any changes where made to the ARP (if not, we do
        // not send the arp
        if (!empty($metadata['arp'])) {
            unset($metadata['arp']);
            $metadata['arp'] = $this->arpMetadataGenerator->build($entity);
        }
        return $metadata;
    }
}
