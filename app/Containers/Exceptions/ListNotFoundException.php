<?php

namespace App\Containers\Exceptions;

class ListNotFoundException extends \Exception
{
    private $listId;

    public function __construct( $listId )
    {
        $this->listId = $listId;
        parent::__construct( $this->__toString(), 404 );
    }
    

    public function __toString()
    {
        return \sprintf('MailChimpList[%s] not found', $this->listId);
    }
}