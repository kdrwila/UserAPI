<?php

namespace App\HttpFoundation;

use Symfony\Component\HttpFoundation\Response;

class XmlResponse extends Response
{
    public function __construct(array $data, string $status, array $headers)
    {
        $output = '<?xml version="1.0" encoding="UTF-8"?>';
        $output .= $this->convertArrayToXmlString($data);
        parent::__construct($output, $status, $headers);
    }

    private function convertArrayToXmlString(array $data, string $rootElement = 'elements') : string
    {
        $output = '';

        foreach($data as $element => $value)
        {
            if(is_numeric($element))
            {
                $element = substr($rootElement, 0, -1);
            }

            if(!is_array($value))
            {
                $output .= "<$element>$value</$element>";
            }
            else
            {
                $output .= "<$element>";
                $output .= $this->convertArrayToXmlString($value, $element);
                $output .= "</$element>";
            }
        }
        
        return $output;
    }
}

?>