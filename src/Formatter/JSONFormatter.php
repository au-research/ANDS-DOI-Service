<?php

namespace ANDS\DOI\Formatter;

class JSONFormatter extends Formatter
{
    /**
     * Format and return the payload
     *
     * @param $payload
     * @return string
     */
    public function format($payload)
    {
        $payload = $this->fill($payload);

        header('Content-type: application/json');

        return json_encode($payload);
    }

}