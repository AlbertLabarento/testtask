<?php

namespace App\Containers\Member;

use Doctrine\ORM\EntityManagerInterface;

class GetMembersTask extends MemberTask
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

    public function run()
    {
        $this->validateResource( $this->listId );

        return parent::getList();
    }
}
