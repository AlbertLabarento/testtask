<?php

namespace App\Containers\Member;

use Doctrine\ORM\EntityManagerInterface;
use App\Database\Entities\Entity;

class UpdateMemberTask extends MemberTask
{
    protected $memberId;

    protected $listId;

    protected $mailChimp;

    protected $validator;

    public function __construct(
        string $listId,
        string $memberId,
        EntityManagerInterface $entityManager,
        \App\Database\Entities\MailChimp\MailChimpListMember $repository,
        \MailChimp\MailChimp $mailChimp,
        \App\Containers\Services\EntityValidator $validator
    ) {
        parent::__construct("lists/$listId/members/$memberId", $entityManager, $repository);
        $this->memberId = $memberId;
        $this->listId = $listId;
        $this->validator = $validator;
        $this->mailChimp = $mailChimp;
    }

    public function run( array $request ) : UpdateMemberTask
    {
        /** @var \App\Database\Entities\MailChimp\MailChimpListMember|null $member */
        $this->findMember = ( new FindMemberTask( $this->listId, $this->entityManager, $this->repository ) )->run($this->memberId);
        
        if ($errors = $this->findMember->hasErrors()) {
            $this->errors = $errors;
            return $this;
        }

        $this->member = $this->findMember->getMemberEntity();

        $this->member->fill($request);

        if( $this->errors = $this->validator->hasError( $this->member ) )
            return $this;

        try {
            // Update list into database
            $this->saveEntity($this->member);
            // Update list into MailChimp
            $this->mailChimp->patch(\sprintf('lists/%s/members/%s', $this->listId, $this->member->getMailChimpId()), $this->member->toMailChimpArray());
        } catch (Exception $exception) {
            return \response()->json( ['message' => json_decode($exception->getMessage(), true)] );
        }

        return $this;
    }
}
