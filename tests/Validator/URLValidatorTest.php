<?php

use ANDS\DOI\Model\ClientDomain;
use ANDS\DOI\Validator\URLValidator;

class URLValidatorTest extends PHPUnit_Framework_TestCase
{
    /** @test * */
    public function it_should_validates_domain_correctly_for_single_domain()
    {
        $domain = "http://devl.ands.org.au/minh/";
        $domains = [
            new ClientDomain(['client_domain' => 'devl.ands.org.au'])
        ];
        $this->assertTrue(URLValidator::validDomains($domain, $domains));
    }

    /** @test * */
    public function it_should_validates_domain_correctly_for_multiple_domain()
    {
        $domain = "http://devl.ands.org.au/minh/";
        $domains = [
            new ClientDomain(['client_domain' => 'researchdata.ands.org.au']),
            new ClientDomain(['client_domain' => 'ands.org.au'])
        ];
        $this->assertTrue(URLValidator::validDomains($domain, $domains));
    }

    /** @test * */
    public function it_should_fail_validation_for_mismatch()
    {
        $domain = "http://devl.ands.org.au/minh/";
        $domains = [
            new ClientDomain(['client_domain' => 'google.com'])
        ];
        $this->assertFalse(URLValidator::validDomains($domain, $domains));
    }
}