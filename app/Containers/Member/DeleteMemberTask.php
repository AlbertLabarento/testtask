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
        parent::__construct($entityManager, $repository, $mailChimp);
        $this->memberId = $memberId;
        $this->listId = $listId;
        $this->mailChimp = $mailChimp;
    }

    public function run() : DeleteMemberTask
    {
        /** @var \App\Database\Entities\MailChimp\MailChimpListMember|null $member */
        $this->member = ( new FindMemberTask( $this->listId, $this->entityManager, $this->repository, $this->mailChimp ) )->run($this->memberId);
        
        $list = $this->getListRepository()->find(  $this->listId  );

        if ($errors = $this->member->hasErrors()) {
            $this->errors = $errors;
            return $this;
        }

        try {
            // Remove list from MailChimp
            $memberId = $this->member->getMemberEntity()->getMailChimpId();
            $this->mailChimp->delete( $this->getResourceUrl( $list->getMailChimpId() , $memberId ) );
            // Remove list from database
            $this->removeEntity($this->member->getMemberEntity());
        } catch (Exception $exception) {
            return \response()->json( ['message' => $exception->getMessage()], 400 );
        }

        return $this;
    }
}
