<?php

namespace ANDS\DOI\Formatter;

class Formatter
{
    /**
     * Helper method to assist with filling out the values in the payload
     *
     * @param $payload
     * @return mixed
     */
    public function fill($payload)
    {
        $payload = $this->determineTypeAndMessage($payload);
        $payload = $this->fillBlanks($payload);
        return $payload;
    }

    /**
     * Helper method to assist in determine the message and type
     * of a DOI response message based on the responsecode
     *
     * @param $payload
     * @return mixed
     */
    public function determineTypeAndMessage($payload)
    {
        switch($payload['responsecode']) {
            case "MT001":
                $payload['message'] = "DOI ".$payload['doi']." was successfully minted.";
                $payload['type'] = "success";
                break;
            case "MT002":
                $payload['message'] = "DOI ".$payload['doi']." was successfully updated.";
                $payload['type'] = "success";
                break;
            case "MT003":
                $payload['message'] = "DOI ".$payload['doi']." was successfully inactivated.";
                $payload['type'] = "success";
                break;
            case "MT004":
                $payload['message'] = "DOI ".$payload['doi']." was successfully activated.";
                $payload['type'] = "success";
                break;
            case "MT005":
                $payload['message'] = "The ANDS Cite My Data service is currently unavailable. Please try again at a later time. If you continue to experience problems please contact services@ands.org.au.";
                $payload['type'] = "failure";
                break;
            case "MT006":
                $payload['message'] = "The metadata you have provided to mint a new DOI has failed the schema validation. 
			Metadata is validated against the latest version of the DataCite Metadata Schema. 
			For information about the schema and the latest version supported, 
			please visit the ANDS website http://ands.org.au. 
			Detailed information about the validation errors can be found below.";
                $payload['type'] = "failure";
                break;
            case "MT007":
                $payload['message'] = "The metadata you have provided to update DOI ".$payload['doi']." has failed the schema validation. 
			Metadata is validated against the DataCite Metadata Schema.
			For information about the schema and the latest version supported, 
			please visit the ANDS website http://ands.org.au. 
			Detailed information about the validation errors can be found below.";
                $payload['type'] = "failure";
                break;
            case "MT008":
                $payload['message'] = "You do not appear to be the owner of DOI ".$payload['doi'].". If you believe this to be incorrect please contact services@ands.org.au.";
                $payload['type'] = "failure";
                break;
            case "MT009":
                $payload['message'] = "You are not authorised to use this service. For more information or to request access to the service please contact services@ands.org.au.";
                $payload['type'] = "failure";
                break;
            case "MT010":
                $payload['message'] = "There has been an unexpected error processing your doi request. For more information please contact services@ands.org.au.";
                $payload['type'] = "failure";
                break;
            case "MT011":
                $payload['message'] = "DOI ".$payload['doi']." does not exist in the ANDS Cite My Data service.";
                $payload['type'] = "failure";
                break;
            case "MT012":
                $payload['message'] = "No metadata exists in the Cite My Data service for DOI ".$payload['doi'];
                $payload['type'] = "failure";
                break;
            case "MT013":
                $payload['message'] = $payload['verbosemessage'];
                $payload['verbosemessage'] = strlen($payload['verbosemessage']) . " bytes";
                $payload['type'] = "success";
                break;
            case "MT014":
                $payload['message'] = "The provided URL does not belong to any of your registered top level domains. If you would like to add additional domains to your account please contact services@ands.org.au. ";
                $payload['type'] = "failure";
                break;
            case "MT090":
                // Success response for status pings (verbose message should indicate ms turnaround time)
                $payload['message'] = "The rocket is ready to blast off -- all systems are go!";
                $payload['type'] = "success";
                break;
            case "MT091":
                // Failure response for status pings
                $payload['message'] = "Uh oh! DOI Service unavailable (unable to process upstream DOI request). Please try again in a few moments. ";
                $payload['type'] = "failure";
                break;
            default:
                $payload['message'] = "There has been an unidentified error processing your doi request. For more information please contact services@ands.org.au.";
                $payload['type'] = "failure";
                break;
        }

        return $payload;
    }

    /**
     * Make sure that all fields are filled out
     *
     * @param $payload
     * @return mixed
     */
    public function fillBlanks($payload)
    {
        $fields = ['doi', 'url', 'message', 'verbosemessage', 'responsecode', 'app_id'];
        foreach ($fields as $field) {
            if (!array_key_exists($field, $payload)) {
                $payload[$field] = "";
            }
        }
        return $payload;
    }
}