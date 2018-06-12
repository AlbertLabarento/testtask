<?php

namespace App\Containers\Member;

use Doctrine\ORM\EntityManagerInterface;

class GetMembersTask extends MemberTask
{
    public function __construct(
        string $listId,
        EntityManagerInterface $entityManager,
        \App\Database\Entities\MailChimp\MailChimpListMember $repository
    ) {
        parent::__construct("list/$listId/members", $entityManager, $repository);
    }

    public function run()
    {
        return parent::getList();
    }
}
