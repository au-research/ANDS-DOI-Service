<?php


namespace ANDS\DOI\Validator;


class XMLValidator
{
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