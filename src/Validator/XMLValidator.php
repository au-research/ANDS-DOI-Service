<?php


namespace ANDS\DOI\Validator;


class XMLValidator
{

    private $validationMessage;

    /**
     * validates datacite xml against required schema version
     *
     * @param $xml
     * @return string
     */

    public function validateSchemaVersion($xml){

        $dataciteSchemaURL =  getenv('DATACITE_SCHEMA_URL');

        $theSchema  = self::getSchemaVersion($xml);

        $doiXML = new \DOMDocument();
        $doiXML->loadXML($xml);
        libxml_use_internal_errors(true);
        $result = $doiXML->schemaValidate($dataciteSchemaURL.$theSchema);

        foreach (libxml_get_errors() as $error) {
            $this->validationMessage = $error->message;
        }

        return $result;

    }



    /**
     * determines datacite xml schema version xsd
     *
     * @param $xml
     * @return string
     */

    public static function getSchemaVersion($xml){

        $doiXML = new \DOMDocument();
        $doiXML->loadXML($xml);

        $resources = $doiXML->getElementsByTagName('resource');
        $theSchema = 'unknown';
        if($resources->length>0)
        {
            if(isset($resources->item(0)->attributes->item(0)->name))
            {
                $theSchema  = substr($resources->item(0)->attributes->item(0)->nodeValue,strpos($resources->item(0)->attributes->item(0)->nodeValue,"/meta/kernel")+5);
            }
        }

        return $theSchema;

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

    /**
     * @return mixed
     */
    public function getValidationMessage()
    {
        return $this->validationMessage;
    }
}