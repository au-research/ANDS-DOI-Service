<?php
/**
 * Created by PhpStorm.
 * User: mnguyen
 * Date: 23/08/2016
 * Time: 3:16 PM
 */

namespace ANDS\DOI\Validator;


class URLValidator
{
    /**
     * Check that a URL can belong to a list of available domains
     *
     * @param $url
     * @param $domains
     * @return bool
     */
    public static function validDomains($url, $domains)
    {
        $theDomain = parse_url($url);
        foreach ($domains as $domain) {
            $check = strpos($theDomain['host'], $domain->client_domain);
            if ($check || $check === 0) {
                return true;
            }
        }
        return false;
    }
}