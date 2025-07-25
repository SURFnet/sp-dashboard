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

use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\ArpGenerator;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\PrivacyQuestionsMetadataGenerator;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\SpDashboardMetadataGenerator;
use Surfnet\ServiceProviderDashboard\Application\Parser\OidcngClientIdParser;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact as ContactEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\ChangeRequestRevisionNote;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\EntityCreationRevisionNote;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\EntityEditRevisionNote;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\JiraTicketNumber;
use Surfnet\ServiceProviderDashboard\Domain\Entity\EntityDiff;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Logo;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\OidcClientInterface;

/**
 * The OidcngJsonGenerator generates oidc10_rp entity json
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ElseExpression)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD)
 */
class OidcngJsonGenerator implements GeneratorInterface
{
    public function __construct(
        private readonly ArpGenerator $arpMetadataGenerator,
        private readonly PrivacyQuestionsMetadataGenerator $privacyQuestionsMetadataGenerator,
        private readonly SpDashboardMetadataGenerator $spDashboardMetadataGenerator,
    ) {
    }

    public function generateForNewEntity(
        ManageEntity $entity,
        string $workflowState,
        ContactEntity $contact,
    ) : array {
        // the type for entities is always saml because manage is using saml internally
        $payload = [
            'data' => $this->generateDataForNewEntity($entity, $workflowState),
            'type' => 'oidc10_rp',
        ];
        $payload['data']['revisionnote'] = (string) new EntityCreationRevisionNote(
            $entity->getComments(),
            $contact->getDisplayName(),
            $contact->getEmailAddress(),
        );
        return $payload;
    }

    public function generateForExistingEntity(
        ManageEntity $entity,
        EntityDiff $differences,
        string $workflowState,
        ContactEntity $contact,
        string $updatedPart = '',
    ): array {
        $data = [
            'pathUpdates' => $this->generateDataForExistingEntity($entity, $differences, $workflowState, $updatedPart),
            'type' => 'oidc10_rp',
            'id' => $entity->getId(),
        ];
        $data['pathUpdates']['revisionnote'] = (string) new EntityEditRevisionNote(
            $entity->getComments(),
            $contact->getDisplayName(),
            $contact->getEmailAddress(),
        );
        return $data;
    }

    public function generateEntityChangeRequest(
        ManageEntity $entity,
        EntityDiff $differences,
        ContactEntity $contact,
        JiraTicketNumber $jiraTicketNumber,
    ): array {
        $revisionNote = (string) new ChangeRequestRevisionNote(
            $entity->getComments(),
            $contact->getDisplayName(),
            $contact->getEmailAddress(),
            $jiraTicketNumber,
        );
        $payload = [
            'metaDataId' => $entity->getId(),
            'type' => 'oidc10_rp',
            'pathUpdates' => $this->generateForChangeRequest($differences, $entity),
            'auditData' => [
                'user' => $contact->getEmailAddress(),
                'notes' => $revisionNote,
            ],
        ];

        $payload['note'] = $revisionNote;

        return $payload;
    }

    private function generateDataForNewEntity(ManageEntity $entity, string $workflowState): array
    {
        // the type for entities is always oidc10-rp because manage is using saml internally
        $metadata = [
            'arp' => $this->arpMetadataGenerator->build($entity),
            'type' => 'oidc10-rp',
            'entityid' => OidcngClientIdParser::parse($entity->getMetaData()->getEntityId()),
            'active' => true,
            'state' => $workflowState,
            'metaDataFields' => $this->generateMetadataFields($entity),
        ];

        $metadata += $this->generateAclData($entity);
        $metadata += $this->generateAllowedResourceServers($entity);

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
            'entityid' => OidcngClientIdParser::parse($entity->getMetaData()->getEntityId()),
        ];
        switch ($updatedPart) {
            case 'ACL':
                return $metadata + $this->generateAclData($entity);

            default:
                $metadata += $differences->getDiff();
                $metadata = $this->generateArp($metadata, $entity);
                $metadata['state'] = $workflowState;

                $metadata += $this->generateAllowedResourceServers($entity);
                $this->setExcludeFromPush($metadata, $entity, true);

                $this->privacyQuestionsMetadataGenerator->withMetadataPrefix();
                $metadata += $this->privacyQuestionsMetadataGenerator->build($entity);

                return $metadata;
        }
    }

    /**
     * @return array
     */
    private function generateMetadataFields(ManageEntity $entity): int|float|array
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
        if ($service->getInstitutionId() && $service->getInstitutionId() !== '') {
            $metadata['coin:institution_id'] = $service->getInstitutionId();
        }
        if ($service->getGuid() !== '') {
            $metadata['coin:institution_guid'] = $service->getGuid();
        }

        $metadata['NameIDFormat'] = $entity->getMetaData()->getNameIdFormat();

        $this->setExcludeFromPush($metadata, $entity);

        $metadata += $this->generateOidcClient($entity);

        if ($entity->getMetaData()->getLogo() instanceof Logo && $entity->getMetaData()->getLogo()->getUrl() !== '') {
            $metadata += $this->generateLogoMetadata($entity);
        }
        if ($entity->getMetaData()?->getCoin()->getContractualBase() !== null) {
            $metadata['coin:contractual_base'] = $entity->getMetaData()->getCoin()->getContractualBase();
        }
        if ($entity->getMetaData()?->getCoin()->isIdpVisibleOnly() !== null) {
            $metadata['coin:ss:idp_visible_only'] = $entity->getMetaData()->getCoin()->isIdpVisibleOnly();
        }
        return $metadata;
    }

    private function generateOidcClient(ManageEntity $entity): array
    {
        $metadata = [];
        if ($entity->getOidcClient() instanceof OidcClientInterface) {
            if (!$entity->getOidcClient()->isPublicClient()) {
                $secret = $entity->getOidcClient()->getClientSecret();
                if ($secret !== '' && $secret !== '0') {
                    $metadata['secret'] = $secret;
                }
            } else {
                $metadata['secret'] = null;
            }
            // Reset the redirect URI list in order to get a correct JSON formatting (See #163646662)
            $metadata['redirectUrls'] = $entity->getOidcClient()->getRedirectUris();
            $metadata['grants'] = $entity->getOidcClient()->getGrants();
            $metadata['accessTokenValidity'] = $entity->getOidcClient()->getAccessTokenValidity();
            $metadata['isPublicClient'] = $entity->getOidcClient()->isPublicClient();
        }
        return $metadata;
    }

    private function generateAllContactsMetadata(ManageEntity $entity): array
    {
        $metadata = [];
        $index = 0;

        $contacts = $entity->getMetaData()->getContacts();

        if ($contacts) {
            if ($contacts->findSupportContact() !== null) {
                $metadata += $this->generateContactMetadata(
                    'support',
                    $index++,
                    $contacts->findSupportContact()
                );
            }

            if ($contacts->findAdministrativeContact() !== null) {
                $metadata += $this->generateContactMetadata(
                    'administrative',
                    $index++,
                    $contacts->findAdministrativeContact()
                );
            }

            if ($contacts->findTechnicalContact() !== null) {
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
        $logoUrl = $entity->getMetaData()->getLogo()->getUrl();
        $metadata = [
            'logo:0:url' => $logoUrl,
        ];

        $logoData = @getimagesize($logoUrl);

        if ($logoData !== false) {
            [$width, $height] = $logoData;
        } else {
            $width = 50;
            $height = 50;
        }

        $metadata['logo:0:width'] = (string) $width;
        $metadata['logo:0:height'] = (string) $height;

        return $metadata;
    }

    private function generateAclData(ManageEntity $entity): array
    {
        $acl = $entity->getAllowedIdentityProviders();
        $providers = [];
        if ($acl) {
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

    private function generateAllowedResourceServers(ManageEntity $entity): array
    {
        $allowedResourceServers = [];
        $client = $entity->getOidcClient();
        if ($client instanceof OidcClientInterface) {
            $collection = $client->getResourceServers();
            if ($collection) {
                foreach ($collection as $clientId) {
                    // When Client resetting, the collection of RS is built of ManageEntities, in other cases
                    // they are entity Id strings
                    if ($clientId instanceof ManageEntity) {
                        $clientId = $clientId->getMetaData()->getEntityId();
                    }
                    $allowedResourceServers[]['name'] = $clientId;
                }
            }
        }
        return [
            'allowedResourceServers' => $allowedResourceServers,
        ];
    }

    private function setExcludeFromPush(array &$metadata, ManageEntity $entity, bool $flatten = false): void
    {
        $fieldName = 'coin:exclude_from_push';
        if ($flatten) {
            $fieldName = 'metaDataFields.coin:exclude_from_push';
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

    private function generateForChangeRequest(EntityDiff $differences, ManageEntity $entity): array
    {
        $metadata = $differences->getDiff();
        return $this->generateArp($metadata, $entity);
    }
}
