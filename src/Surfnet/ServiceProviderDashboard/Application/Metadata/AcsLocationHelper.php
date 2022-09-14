<?php

namespace Surfnet\ServiceProviderDashboard\Application\Metadata;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\MetaData;

class AcsLocationHelper
{
    /**
     * Remove empty and null elements from an array.
     *
     * @param $array
     * @return array
     */
    public static function cleanArray(&$array)
    {
        return array_values(array_filter($array));
    }
    /**
     * The binding of the ACS URL is always POST.
     *
     * When importing XML metadata (Legacy\Metadata\Parser) the dashboard only
     * imports the POST ACS URLs. Other formats are not supported by manage or
     * the dashboard.
     *
     * @param $acsLocations
     * @param $metadata
     * @return void
     */
    public static function addAcsLocationsToMetaData($acsLocations, &$metadata)
    {
        $ascLocations = array_values(array_filter($acsLocations));
        foreach ($ascLocations as $index => $acsLocation) {
            $metadata['AssertionConsumerService:' . $index . ':Binding'] = Constants::BINDING_HTTP_POST;
            $metadata['AssertionConsumerService:' . $index . ':Location'] = $acsLocation;
        }
    }

    /**
     * Add empty remaining locations so Manage can delete them
     *
     * @param $acsLocations
     * @param $metadata
     * @return void
     */
    public static function addEmptyAscLocationsToMetaData($acsLocations, &$metadata)
    {
        $index = count(array_values(array_filter($acsLocations)));
        while ($index < MetaData::MAX_ACS_LOCATIONS) {
            $metadata['AssertionConsumerService:' . $index . ':Binding'] = null;
            $metadata['AssertionConsumerService:' . $index . ':Location'] = null;
            $index++;
        }
    }
}
