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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;

/**
 * The JsonGenerator is able to generate Manage SAML 2.0 JSON metadata
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ElseExpression)
 */
class JsonGenerator implements GeneratorInterface
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

    public function __construct(
        ArpGenerator $arpMetadataGenerator,
        PrivacyQuestionsMetadataGenerator $privacyQuestionsMetadataGenerator,
        SpDashboardMetadataGenerator $spDashboardMetadataGenerator
    ) {
        $this->arpMetadataGenerator = $arpMetadataGenerator;
        $this->privacyQuestionsMetadataGenerator = $privacyQuestionsMetadataGenerator;
        $this->spDashboardMetadataGenerator = $spDashboardMetadataGenerator;
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
        string $workflowState,
        string $updatedPart = ''
    ): array {
        // the type for entities is always saml because manage is using saml internally
        $data = [
            'pathUpdates' => $this->generateDataForExistingEntity($entity, $workflowState, $updatedPart),
            'type' => 'saml20_sp',
            'id' => $entity->getId(),
        ];


        return $data;
    }

    /**
     * @param ManageEntity $entity
     * @param string $workflowState
     * @return array
     */
    private function generateDataForNewEntity(ManageEntity $entity, $workflowState)
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


        if ($entity->hasComments()) {
            $metadata['revisionnote'] = $entity->getComments();
        }

        return $metadata;
    }

    private function generateDataForExistingEntity(
        ManageEntity $entity,
        string $workflowState,
        string $updatedPart
    ): array {
        $metadata = [
            'entityid' => $entity->getMetaData()->getEntityId(),
        ];

        switch ($updatedPart) {
            case 'ACL':
                $metadata += $this->generateAclData($entity);
                break;

            default:
                $metadata['arp'] = $this->arpMetadataGenerator->build($entity);
                $metadata['state'] = $workflowState;

                $metadata += $this->flattenMetadataFields(
                    $this->generateMetadataFields($entity)
                );

                if ($entity->getProtocol()->getProtocol() === Constants::TYPE_SAML) {
                    $metadata['metadataurl'] = $entity->getMetaData()->getMetadataUrl();
                }
        }

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
            $flatFields['metaDataFields.' . $name] = $value;
        }

        return $flatFields;
    }

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
        if ($service->getInstitutionId() && $service->getInstitutionId() != '') {
            $metadata['coin:institution_id'] = $service->getInstitutionId();
        }
        if ($service->getGuid() != '') {
            $metadata['coin:institution_guid'] = $service->getGuid();
        }

        /*
         * The binding of the ACS URL is always POST.
         *
         * When importing XML metadata (Legacy\Metadata\Parser) the dashboard only
         * imports the POST ACS URL. Other formats are not supported by manage or
         * the dashboard.
         */
        $metadata['AssertionConsumerService:0:Binding'] = Constants::BINDING_HTTP_POST;
        $metadata['AssertionConsumerService:0:Location'] = $entity->getMetaData()->getAcsLocation();
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
        if ($entity->getMetaData()->getLogo() !== null && $entity->getMetaData()->getLogo()->getUrl() !== '') {
            $metadata = array_merge($metadata, $this->generateLogoMetadata($entity));
        }

        return $metadata;
    }

    private function generateCertDataMetadata(ManageEntity $entity): array
    {
        $metadata = [];
        if (!empty($entity->getMetaData()->getCertData())) {
            $metadata['certData'] = $this->stripCertificateEnvelope(
                $entity->getMetaData()->getCertData()
            );
        }

        return $metadata;
    }

    /**
     * Strip header and footer from certificate data.
     *
     * @param string $certData
     * @return string
     */
    private function stripCertificateEnvelope($certData)
    {
        $certData = str_replace('-----BEGIN CERTIFICATE-----', '', $certData);
        $certData = str_replace('-----END CERTIFICATE-----', '', $certData);

        return trim($certData);
    }

    /**
     * @param ManageEntity $entity
     * @return array
     */
    private function generateAllContactsMetadata(ManageEntity $entity)
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

        if ($entity->getMetaData()->getContacts()->findAdministrativeContact()) {
            $metadata += $this->generateContactMetadata(
                'administrative',
                $index++,
                $entity->getMetaData()->getContacts()->findAdministrativeContact()
            );
        }

        if ($entity->getMetaData()->getContacts()->findTechnicalContact()) {
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

    /**
     * Generate logo metadata fields.
     *
     * Logo dimensions are required in the SAML spec. They are always present,
     * except when the user just created the entity in the interface. We
     * determine the dimensions in those situations.
     *
     * @param ManageEntity $entity
     * @return array
     */
    private function generateLogoMetadata(ManageEntity $entity)
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
                list($width, $height) = $logoData;
            } else {
                $width = 50;
                $height = 50;
            }

            $metadata['logo:0:width'] = (string)$width;
            $metadata['logo:0:height'] = (string)$height;
        }
        return $metadata;
    }


    /**
     * @param ManageEntity $entity
     * @return array
     */
    private function generateAclData(ManageEntity $entity)
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
}
