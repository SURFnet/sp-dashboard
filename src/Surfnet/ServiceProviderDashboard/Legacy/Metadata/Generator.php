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

use InvalidArgumentException;
use SimpleXMLElement;
use Surfnet\ServiceProviderDashboard\Application\Metadata\GeneratorInterface;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\AttributesMetadataRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ElseExpression)
 */
class Generator implements GeneratorInterface
{
    const NS_SAML = 'urn:oasis:names:tc:SAML:2.0:metadata';
    const NS_SIG = 'http://www.w3.org/2000/09/xmldsig#';
    const NS_UI = 'urn:oasis:names:tc:SAML:metadata:ui';
    const NS_LANG = 'http://www.w3.org/XML/1998/namespace';

    /**
     * @var AttributesMetadataRepository
     */
    private $attributesMetadataRepository;

    public function __construct(
        AttributesMetadataRepository $attributesMetadataRepository
    ) {
        $this->attributesMetadataRepository = $attributesMetadataRepository;
    }

    /**
     * @param Service $service
     *
     * @return string
     */
    public function generate(Service $service)
    {
        $xml = $service->getMetadataXml();

        if (empty($xml)) {
            throw new InvalidArgumentException(
                'Subscription without metadata xml: ' . $service->getId()
            );
        }

        $xml = simplexml_load_string($xml);

        if (!$xml instanceof SimpleXMLElement) {
            throw new InvalidArgumentException(
                'Subscription without invalid xml: ' . $service->getId()
            );
        }

        $children = $xml->children(self::NS_SAML);

        // Remove the Signature if it exists.
        unset($children->Signature);

        /** @var SimpleXMLElement $descriptor */
        $descriptor = $children->SPSSODescriptor;

        // Remove the Signature if it exists.
        unset($descriptor->Signature);

        $this->generateUi($descriptor, $service);
        $this->generateContacts($xml, $service);
        $this->generateAttributes($descriptor, $service);

        return $xml->asXML();
    }

    /**
     * @param SimpleXMLElement $xml
     * @param Service $service
     */
    private function generateUi(SimpleXMLElement $xml, Service $service)
    {
        $extensions = $this->setNode($xml, 'md:Extensions', null, array(), array('md' => self::NS_SAML), array(), 0);
        $ui = $this->setNode($extensions, 'ui:UIInfo', null, array(), array('ui' => self::NS_UI));

        $this->generateLogo($ui, $service);

        $this->setNode(
            $ui,
            'ui:Description',
            $service->getDescriptionEn(),
            array('xml:lang' => 'en'),
            array('ui' => self::NS_UI),
            array('xml' => self::NS_LANG)
        );

        $this->setNode(
            $ui,
            'ui:Description',
            $service->getDescriptionNl(),
            array('xml:lang' => 'nl'),
            array('ui' => self::NS_UI),
            array('xml' => self::NS_LANG)
        );

        $this->setNode(
            $ui,
            'ui:DisplayName',
            $service->getNameEn(),
            array('xml:lang' => 'en'),
            array('ui' => self::NS_UI),
            array('xml' => self::NS_LANG)
        );

        $this->setNode(
            $ui,
            'ui:DisplayName',
            $service->getNameNl(),
            array('xml:lang' => 'nl'),
            array('ui' => self::NS_UI),
            array('xml' => self::NS_LANG)
        );

        $this->setNode(
            $ui,
            'ui:InformationURL',
            $service->getApplicationUrl(),
            array('xml:lang' => 'en'),
            array('ui' => self::NS_UI),
            array('xml' => self::NS_LANG)
        );
    }

    /**
     * @param SimpleXMLElement $xml
     * @param Service $service
     */
    private function generateLogo(SimpleXMLElement $xml, Service $service)
    {
        $logo = $service->getLogoUrl();
        if (empty($logo)) {
            $this->removeNode($xml, 'ui:Logo', array(), array('ui' => self::NS_UI));

            return;
        }

        $node = $this->setNode(
            $xml,
            'ui:Logo',
            $logo,
            array(),
            array('ui' => self::NS_UI)
        );

        $logoData = @getimagesize($logo);

        if ($logoData !== false) {
            list($width, $height) = $logoData;

            $node['width'] = $width;
            $node['height'] = $height;
        }
    }

    /**
     * @param SimpleXMLElement $xml
     * @param Service $service
     */
    private function generateContacts(SimpleXMLElement $xml, Service $service)
    {
        if ($service->getSupportContact() instanceof Contact) {
            $this->generateContact($xml, $service->getSupportContact(), 'support');
        }

        if ($service->getTechnicalContact() instanceof Contact) {
            $this->generateContact($xml, $service->getTechnicalContact(), 'technical');
        }

        if ($service->getAdministrativeContact() instanceof Contact) {
            $this->generateContact($xml, $service->getAdministrativeContact(), 'administrative');
        }
    }

    /**
     * @param SimpleXMLElement $xml
     * @param Contact $contact
     * @param $type
     */
    private function generateContact(SimpleXMLElement $xml, Contact $contact, $type)
    {
        $node = $this->setNode(
            $xml,
            'md:ContactPerson',
            '',
            array('contactType' => $type),
            array('md' => self::NS_SAML)
        );

        $this->setNode($node, 'md:GivenName', $contact->getFirstName(), array(), array('md' => self::NS_SAML));
        $this->setNode($node, 'md:SurName', $contact->getLastName(), array(), array('md' => self::NS_SAML));
        $this->setNode($node, 'md:EmailAddress', $contact->getEmail(), array(), array('md' => self::NS_SAML));
        $this->setNode($node, 'md:TelephoneNumber', $contact->getPhone(), array(), array('md' => self::NS_SAML));
    }

    /**
     * @param SimpleXMLElement $xml
     * @param Service $service
     */
    private function generateAttributes(SimpleXMLElement $xml, Service $service)
    {
        if (!$this->hasRequestedAttributes($service)) {
            $this->removeNode($xml, 'md:AttributeConsumingService', array(), array('md' => self::NS_SAML));

            return;
        }

        $node = $this->setNode(
            $xml,
            'md:AttributeConsumingService',
            null,
            array('index' => 0),
            array('md' => self::NS_SAML)
        );

        if (null === $this->findNode($node, 'md:ServiceName', array(), array('md' => self::NS_SAML))) {
            $this->setNode(
                $node,
                'md:ServiceName',
                $service->getNameEn(),
                array('xml:lang' => 'en'),
                array('md' => self::NS_SAML),
                array('xml' => self::NS_LANG),
                0
            );
        }

        $attributesMetadata = $this->attributesMetadataRepository->findAll();
        foreach ($attributesMetadata as $attributeMetadata) {
            $getterName = 'get' . ucfirst($attributeMetadata->id) . 'Attribute';

            // Skip attributes we know about but don't have registered.
            if (!method_exists($service, $getterName)) {
                continue;
            }

            $attr = $service->$getterName();

            if ($attr instanceof Attribute && $attr->isRequested()) {
                $this->generateAttribute($node, $attributeMetadata->urns, $attributeMetadata->friendlyName);
            } else {
                $this->removeAttribute($node, $attributeMetadata->urns);
            }
        }
    }

    /**
     * @param Service $service
     *
     * @return bool
     */
    private function hasRequestedAttributes(Service $service)
    {
        $attributesMetadata = $this->attributesMetadataRepository->findAll();

        foreach ($attributesMetadata as $attributeMetadata) {
            $getterName = 'get' . ucfirst($attributeMetadata->id) . 'Attribute';

            // Skip attributes we know about but don't have registered.
            if (!method_exists($service, $getterName)) {
                continue;
            }

            $attr = $service->$getterName();

            if ($attr instanceof Attribute && $attr->isRequested()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param SimpleXMLElement $xml
     * @param array             $names
     * @param string            $friendlyName
     */
    private function generateAttribute(SimpleXMLElement $xml, array $names, $friendlyName)
    {
        // First try to find an existing node
        foreach ($names as $name) {
            $node = $this->findNode(
                $xml,
                'md:RequestedAttribute',
                array('Name' => $name),
                array('md' => self::NS_SAML)
            );

            if ($node !== null) {
                $node['FriendlyName'] = $friendlyName;

                return;
            }
        }

        // If no existing node has been found, create and set one with the first name from the supplied names
        $this->setNode(
            $xml,
            'md:RequestedAttribute',
            null,
            array('Name' => $names[0], 'FriendlyName' => $friendlyName),
            array('md' => self::NS_SAML)
        );
    }

    /**
     * @param SimpleXMLElement $xml
     * @param array             $names
     */
    private function removeAttribute(SimpleXMLElement $xml, array $names)
    {
        foreach ($names as $name) {
            $node = $this->findNode(
                $xml,
                'md:RequestedAttribute',
                array('Name' => $name),
                array('md' => self::NS_SAML)
            );

            if ($node !== null) {
                unset($node[0]);
            }
        }
    }

    /**
     * Update (or Add if it not exists) a child node with the specified value
     *
     * @param SimpleXMLElement $rootNode
     * @param string            $nodeName
     * @param string            $value
     * @param array             $attributes
     * @param array             $cnss     child namespaces
     * @param array             $anss     attribute namespaces
     * @param null              $position to add the element, if null, it will be appended to rootNode
     *
     * @return SimpleXMLElement
     */
    private function setNode(
        SimpleXMLElement $rootNode,
        $nodeName,
        $value = null,
        $attributes = array(),
        $cnss = array(),
        $anss = array(),
        $position = null
    ) {
        $node = $this->findNode($rootNode, $nodeName, $attributes, array_merge($cnss, $anss));

        if (isset($node)) {
            if ($value !== null) {
                $node[0] = $value;
            }

            return $node;
        }

        return $this->addNode($rootNode, $nodeName, $value, $attributes, $cnss, $anss, $position);
    }

    /**
     * @param SimpleXMLElement $rootNode
     * @param string            $nodeName
     * @param array             $attributes
     * @param array             $nss
     *
     * @return null|SimpleXMLElement
     */
    private function findNode(
        SimpleXMLElement $rootNode,
        $nodeName,
        $attributes = array(),
        $nss = array()
    ) {
        $xpathExpression = './' . $nodeName;

        foreach ($attributes as $aName => $aValue) {
            $xpathExpression .= '[@' . $aName . '=\'' . $aValue . '\']';
        }

        foreach ($nss as $alias => $ns) {
            $rootNode->registerXPathNamespace($alias, $ns);
        }

        $node = $rootNode->xpath($xpathExpression);

        if (isset($node[0][0])) {
            return $node[0];
        }

        return null;
    }

    /**
     * @param SimpleXMLElement $rootNode
     * @param string            $nodeName
     * @param null              $value
     * @param array             $attributes
     * @param array             $cnss
     * @param array             $anss
     * @param null              $position
     *
     * @return SimpleXMLElement
     */
    private function addNode(
        SimpleXMLElement $rootNode,
        $nodeName,
        $value = null,
        $attributes = array(),
        $cnss = array(),
        $anss = array(),
        $position = null
    ) {
        $ns = count($cnss) > 0 ? reset($cnss) : null;
        $node = $this->addChildNodeAt($rootNode, $nodeName, $value, $ns, $position);

        foreach ($attributes as $aName => $aValue) {
            $ns = count($anss) > 0 ? reset($anss) : null;
            $node->setAttributeNS($ns, $aName, $aValue);
        }

        return simplexml_import_dom($node);
    }

    /**
     * @param SimpleXMLElement $rootNode
     * @param string            $nodeName
     * @param array             $attributes
     * @param array             $cnss
     * @param array             $anss
     */
    private function removeNode(
        SimpleXMLElement $rootNode,
        $nodeName,
        $attributes = array(),
        $cnss = array(),
        $anss = array()
    ) {
        $node = $this->findNode($rootNode, $nodeName, $attributes, array_merge($cnss, $anss));

        if ($node !== null) {
            unset($node[0]);
        }
    }

    /**
     * @param SimpleXMLElement $parent
     * @param string            $nodeName
     * @param string            $value
     * @param string            $ns
     * @param int               $position
     *
     * @return \DOMElement
     */
    private function addChildNodeAt(SimpleXMLElement $parent, $nodeName, $value = null, $ns = null, $position = null)
    {
        $parent = dom_import_simplexml($parent);

        $child = new \DOMElement($nodeName, $value, $ns);
        $child = $parent->ownerDocument->importNode($child, true);

        if ($position === null || $parent->childNodes->item($position) === null) {
            return $parent->appendChild($child);
        } else {
            return $parent->insertBefore($child, $parent->childNodes->item($position));
        }
    }
}
