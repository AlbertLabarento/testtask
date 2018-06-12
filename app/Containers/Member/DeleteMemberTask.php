<?php

namespace App\Containers\Member;

use Doctrine\ORM\EntityManagerInterface;
use App\Database\Entities\Entity;

class DeleteMemberTask extends MemberTask
{
    protected $memberId;

    protected $listId;

    protected $mailChimp;

    public function __construct(
        string $listId,
        string $memberId,
        EntityManagerInterface $entityManager,
        \App\Database\Entities\MailChimp\MailChimpListMember $repository,
        \Mailchimp\Mailchimp $mailChimp
    ) {
        parent::__construct("list/$listId/members/$memberId", $entityManager, $repository);
        $this->memberId = $memberId;
        $this->listId = $listId;
        $this->mailChimp = $mailChimp;
    }

    public function run() : DeleteMemberTask
    {
        /** @var \App\Database\Entities\MailChimp\MailChimpListMember|null $member */
        $this->member = app()->make( FindMemberTask::class, [ 'listId' => $this->listId ] )->run( $this->memberId );
        
        if ($errors = $this->member->hasErrors()) {
            $this->errors = $errors;
            return $this;
        }

        try {
            // Remove list from MailChimp
            $memberId = $this->member->getMemberEntity()->getMailChimpId();
            $this->mailChimp->delete("lists/$this->listId/members/$memberId");
            // Remove list from database
            $this->removeEntity($this->member->getMemberEntity());
        } catch (Exception $exception) {
            return \response()->json( ['message' => $exception->getMessage()], 400 );
        }

        return $this;
    }
}
