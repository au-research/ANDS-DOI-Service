<?php


namespace ANDS\DOI\Validator;


class XMLValidator
{
    /**
     * determines datacite xml schema version
     *
     * @param $xml
     * @return string
     */

    public function getSchemaVersion($xml){

        $doiXML = new \DOMDocument();
        $doiXML->loadXML($xml);

        $resources = $doiXML->getElementsByTagName('resource');
        $theSchema = 'unknown';
        if($resources->length>0)
        {
            if(isset($resources->item(0)->attributes->item(0)->name))
            {
                $theSchema  = self::getXmlSchema($resources->item(0)->attributes->item(0)->nodeValue);
            }
        }
        return $theSchema;

    }


    /**
     * Helper function to determine version number from xml resource string
     *
     * @param $theSchemaLocation
     * @return string
     */

    public static function getXmlSchema($theSchemaLocation)
    {
        if(str_replace("kernel-2.0","",$theSchemaLocation)!=$theSchemaLocation)
        {
            return "2.0";
        }
        elseif(str_replace("kernel-2.1","",$theSchemaLocation)!=$theSchemaLocation)
        {
            return "2.1";
        }
        elseif(str_replace("kernel-2.2","",$theSchemaLocation)!=$theSchemaLocation)
        {
            return "2.2";
        }
        elseif(str_replace("kernel-3","",$theSchemaLocation)!=$theSchemaLocation)
        {
            return "3";
        }
        else
        {
            return "unknown";
        }
    }

    /**
     * Replaces the DOI Identifier value in the provided XML
     *
     * @param $doiValue
     * @param $xml
     * @return string
     */

    public static function replaceDOIValue($doiValue, $xml)
    {
        $doiXML = new \DOMDocument();
        $doiXML->loadXML($xml);

        // remove the current identifier
        $currentIdentifier = $doiXML->getElementsByTagName('identifier');
        for ($i = 0; $i < $currentIdentifier->length; $i++) {
            $doiXML
                ->getElementsByTagName('resource')
                ->item(0)
                ->removeChild($currentIdentifier->item($i));
        }

        // add new identifier to the DOM
        $newIdentifier = $doiXML->createElement('identifier', $doiValue);
        $newIdentifier->setAttribute('identifierType', "DOI");
        $doiXML
            ->getElementsByTagName('resource')
            ->item(0)
            ->insertBefore(
                $newIdentifier,
                $doiXML->getElementsByTagName('resource')->item(0)->firstChild
            );

        return $doiXML->saveXML();
    }
}