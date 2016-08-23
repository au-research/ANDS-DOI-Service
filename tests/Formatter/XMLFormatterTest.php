<?php
use ANDS\DOI\Formatter\XMLFormatter;

/**
 * Created by PhpStorm.
 * User: mnguyen
 * Date: 23/08/2016
 * Time: 8:58 AM
 */
class XMLFormatterTest extends PHPUnit_Framework_TestCase
{
    /** @test **/
    public function it_should_be_able_to_format_a_standard_message()
    {
        $formatter = $this->getFormatter();
        $payload = [
            'type' => 'success',
            'responsecode' => 'MT001',
            'message' => 'DOI was minted successfully',
            'doi' => 'DOI',
            'url' => 'url',
            'app_id' => 'app_id',
            'verbosemessage' => 'verbosemessage'
        ];
        $message = $formatter->format($payload);

    }

    private function getFormatter()
    {
        $formatter = new XMLFormatter();
        return $formatter;
    }
}