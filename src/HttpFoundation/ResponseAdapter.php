<?php

namespace App\HttpFoundation;

use App\HttpFoundation\XmlResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

class ResponseAdapter
{
    private $data;
    private $status;
    private $headers;
    private $type;

    public function __construct(array $data, int $status, array $headers = array(), string $type = 'json')
    {
        $this->data = $data;
        $this->status = $status;
        $this->headers = $headers;
        $this->type = $type;
    }

    public function returnResponse()
    {
        switch($this->type)
        {
            case 'xml':
                $this->data = array('response' => $this->data);
                $this->headers['Content-type'] = 'text\xml';
                return new XmlResponse($this->data, $this->status, $this->headers);
            
            default:
                return new JsonResponse($this->data, $this->status, $this->headers);
        }
    }
}

?>