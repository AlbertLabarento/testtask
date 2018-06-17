<?php

namespace App\Containers\Exceptions;

class MemberNotFoundException extends \Exception
{
    private $memberId;

    public function __construct( $message, $memberId )
    {
        $this->memberId = $memberId;
        parent::__construct( $this->__toString(), 404 );
    }
    

    public function __toString()
    {
        return \sprintf('MailChimpListMember[%s] not found', $this->memberId);
    }
}