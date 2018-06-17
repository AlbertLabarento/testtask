<?php

namespace App\Containers\Member;

use Doctrine\ORM\EntityManagerInterface;
use App\Database\Entities\Entity;

class FindMemberTask extends MemberTask
{
    public function __construct(
        string $listId,
        EntityManagerInterface $entityManager,
        \App\Database\Entities\MailChimp\MailChimpListMember $repository,
        \Mailchimp\Mailchimp $mailChimp
    ) {
        parent::__construct($entityManager, $repository, $mailChimp);
        $this->listId = $listId;
    }

    public function run( string $id ) : FindMemberTask
    {
        $this->validateResource( $this->listId );

        $this->member = parent::getMember( $id );

        if ($this->member === null) {
            $this->errors = ['message' => \sprintf('MailChimpListMember[%s] not found', $id)];
            return $this;
        }
        

        return $this;
    }
}
