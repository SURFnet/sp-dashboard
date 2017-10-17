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

namespace Surfnet\ServiceProviderDashboard\Legacy\Metadata;

use Surfnet\ServiceProviderDashboard\Application\Metadata\GeneratorInterface;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;

class JsonGenerator implements GeneratorInterface
{
    /**
     * @param Service $service
     * @return string
     */
    public function generate(Service $service)
    {
        return json_encode([
            'id' => $service->getId(), // This might not be wise
//            'version' => 0,
            'type' => 'saml20_sp',
            'revision' => $this->buildRevision($service),
            'data' => [
                'entityid' => $service->getEntityId(),
                'revisionid' => 0,
                'state' => 'prodaccepted',
                'type' => 'saml20-sp',
                'metadataurl' => $service->getMetadataUrl(),
                'allowedall' => true,
                'manipulation' => null,
//                'user' => 'admin',
                'active' => true,
                'arp' => $this->buildArp($service),
                'notes' => $service->getComments(),
                'metaDataFields' => $this->buildMetadata($service),
                'allowedEntities' => [],
            ],
        ]);
    }

    private function buildRevision(Service $service)
    {
        return [
//            'number' => 0,
            'created' => $service->getCreated()->getTimestamp(),
            'parentId' => null,
//            'updatedBy' => 'admin',
        ];
    }

    private function buildMetadata(Service $service)
    {
        $metadata = [
            'name:en' => $service->getNameEn(),
            'name:nl' => $service->getNameNl(),
            'description:en' => $service->getDescriptionEn(),
            'description:nl' => $service->getDescriptionNl(),
            'AssertionConsumerService:0:Location' => $service->getAcsLocation(),
            'AssertionConsumerService:0:Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',

//            'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
//            'NameIDFormats:0' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
//            'NameIDFormats:1' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
//            'NameIDFormats:2' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
//            'displayName:en' => 'OpenConext Profile',
        ];

        $logoData = @getimagesize($service->getLogoUrl());

        if ($logoData !== false) {
            list($width, $height) = $logoData;

            $metadata['logo:0:url'] = $service->getLogoUrl();
            $metadata['logo:0:width'] = (string) $width;
            $metadata['logo:0:height'] = (string) $height;
        }

        $this->addContact($metadata, 0,'administrative', $service->getAdministrativeContact());
        $this->addContact($metadata, 1,'support', $service->getSupportContact());
        $this->addContact($metadata, 2,'technical', $service->getTechnicalContact());

        return $metadata;
    }

    /**
     * @todo
     * @param Service $service
     * @return array
     */
    private function buildArp(Service $service)
    {
        return [
            'attributes' =>  [],
            'enabled' => false,
        ];
    }

    /**
     * @param array $metadata
     * @param string $type
     * @param Contact|null $contact
     */
    private function addContact(array &$metadata, $index, $type, $contact)
    {
        if ($contact) {
            $metadata['contacts:' . $index . ':givenName'] = $contact->getFirstName();
            $metadata['contacts:' . $index . ':surName'] = $contact->getLastName();
            $metadata['contacts:' . $index . ':emailAddress'] = $contact->getEmail();
            $metadata['contacts:' . $index . ':telephoneNumber'] = $contact->getPhone();
            $metadata['contacts:' . $index . ':contactType'] = $type;
        }
    }
}
