<?php

namespace Manowartop\ServiceRepositoryPattern\Exceptions\Service;

use Exception;
use Illuminate\Http\Response;

/**
 * Class ServiceException
 * @package Manowartop\ServiceRepositoryPattern\Exceptions\Service
 */
class ServiceException extends Exception
{
    /**
     * ServiceException constructor.
     * @param string $message
     */
    public function __construct($message = "Service Exception")
    {
        parent::__construct($message, Response::HTTP_BAD_REQUEST);
    }
}
