<?php

namespace ANDS\DOI\Formatter;


class XMLFormatter extends Formatter
{
    public function format($payload)
    {
        $str = "";
        $str .= "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>";
        $str .="<response type=\"".$payload['type']."\">";
        $str .="<responsecode>".$payload['responsecode']."</responsecode>";
        $str .="</response>";
        return $str;
    }

}