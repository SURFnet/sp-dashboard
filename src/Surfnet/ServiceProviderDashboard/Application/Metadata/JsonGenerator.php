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

use Surfnet\ServiceProviderDashboard\Application\Dto\MetadataConversionDto;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\ArpGenerator;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\PrivacyQuestionsMetadataGenerator;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\SpDashboardMetadataGenerator;
use Surfnet\ServiceProviderDashboard\Application\Parser\OidcClientIdParser;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;

/**
 * The JsonGenerator is able to generate Manage SAML 2.0 and OpenID Connect entities (oidc).
 *
 * The oidc entities are actually saved as saml20 entities. This is an early implementation detail in Manage < v4.
 * From version 4 and onward the oidcng (oidc10_rp) entity type is introduced. This generator is not capable of
 * creating that type of json.
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
     * @param string $oidcPlaygroundUriTest OIDC playgroudn uri
     * @param string $oidcPlaygroundUriProd OIDC playgroudn uri
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
            'type' => 'saml20_sp',
        ];
    }

    /**
     * @param MetadataConversionDto $entity
     * @param string $workflowState
     * @return array
     */
    public function generateForExistingEntity(MetadataConversionDto $entity, $workflowState)
    {
        // the type for entities is always saml because manage is using saml internally
        $data = [
            'pathUpdates' => $this->generateDataForExistingEntity($entity, $workflowState),
            'type' => 'saml20_sp',
            'id' => $entity->getManageId(),
        ];

        if ($entity->getProtocol() === Constants::TYPE_OPENID_CONNECT) {
            $data['externalReferenceData'] = [
                'oidcClient' => $this->generateOidcClient($entity),
            ];
        }

        return $data;
    }

    /**
     * @param MetadataConversionDto $entity
     * @param string $workflowState
     * @return array
     */
    private function generateDataForNewEntity(MetadataConversionDto $entity, $workflowState)
    {
        // the type for entities is always saml because manage is using saml internally
        $metadata = [
            'arp' => $this->arpMetadataGenerator->build($entity),
            'type' => 'saml20-sp',
            'entityid' => $entity->getEntityId(),
            'active' => true,
            'state' => $workflowState,
            'metaDataFields' => $this->generateMetadataFields($entity),
        ];

        $metadata += $this->generateAclData($entity);

        switch (true) {
            case ($entity->getProtocol() == Constants::TYPE_SAML):
                $metadata['metadataurl'] = $entity->getMetadataUrl();
                break;
            case ($entity->getProtocol() == Constants::TYPE_OPENID_CONNECT):
                $metadata['oidcClient'] = $this->generateOidcClient($entity);
                break;
        }

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
            'entityid' => $entity->getEntityId(),
            'state' => $workflowState,
        ];

        $metadata += $this->generateAclData($entity);

        if ($entity->getProtocol() == Constants::TYPE_SAML) {
            $metadata['metadataurl'] = $entity->getMetadataUrl();
        }

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
            $flatFields['metaDataFields.' . $name] = $value;
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

        $service = $entity->getService();
        if ($service->getInstitutionId() != '') {
            $metadata['coin:institution_id'] = $service->getInstitutionId();
        }
        if ($service->getGuid() != '') {
            $metadata['coin:institution_guid'] = $service->getGuid();
        }

        if ($entity->getProtocol() === Constants::TYPE_SAML) {
            $metadata['AssertionConsumerService:0:Binding'] = $entity->getAcsBinding();
            $metadata['AssertionConsumerService:0:Location'] = $entity->getAcsLocation();
            $metadata['NameIDFormat'] = $entity->getNameIdFormat();
            $metadata['coin:signature_method'] = 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256';
            $metadata = array_merge($metadata, $this->generateCertDataMetadata($entity));
        } else if ($entity->getProtocol() === Constants::TYPE_OPENID_CONNECT) {
            $metadata['coin:signature_method'] = 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256';
            $metadata["coin:oidc_client"] = '1';
        }

        // When publishing to production, the coin:exclude_from_push must be present and set to '1'. This prevents the
        // entity from being pushed to EngineBlock. Once the entity is checked a final time, the flag is set to 0
        // by one of the administrators. If the entity was included for push, we make sure it is not overridden.
        if ($entity->isProduction()) {
            $metadata['coin:exclude_from_push'] = '1';
        }
        if ($entity->isManageEntity() && !$entity->isExcludedFromPush()) {
            $metadata['coin:exclude_from_push'] = '0';
        }
        if (!empty($entity->getLogoUrl())) {
            $metadata = array_merge($metadata, $this->generateLogoMetadata($entity));
        }

        return $metadata;
    }

    /**
     * @param Entity $entity
     * @return array
     */
    private function generateCertDataMetadata(MetadataConversionDto $entity)
    {
        $metadata = [];
        if (!empty($entity->getCertificate())) {
            $metadata['certData'] = $this->stripCertificateEnvelope(
                $entity->getCertificate()
            );
        }

        return $metadata;
    }

    /**
     * @param Entity $entity
     * @return array
     */
    private function generateOidcClient(MetadataConversionDto $entity)
    {
        $metadata = [];
        $metadata['clientId'] = OidcClientIdParser::parse($entity->getEntityId());
        $metadata['clientSecret'] = $entity->getClientSecret();
        // Reset the redirect URI list in order to get a correct JSON formatting (See #163646662)
        $metadata['redirectUris'] = $entity->getRedirectUris();
        $metadata['grantType'] = $entity->getGrantType()->getGrantType();
        $metadata['scope'] = ['openid'];

        if ($entity->isEnablePlayground()) {
            if ($entity->isProduction()) {
                $metadata['redirectUris'][] = $this->oidcPlaygroundUriProd;
            } else {
                $metadata['redirectUris'][] = $this->oidcPlaygroundUriTest;
            }
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
     * @param Entity $entity
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
}
