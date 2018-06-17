<?php

namespace App\Containers\Member;

use App\Database\Entities\Entity;

use Doctrine\ORM\EntityManagerInterface;
use App\Containers\Services\EntityValidator;
use Mailchimp\Mailchimp;

class CreateMemberTask extends MemberTask
{
    private $validator;

    public function __construct(
        string $listId,
        EntityManagerInterface $entityManager,
        \App\Database\Entities\MailChimp\MailChimpListMember $repository,
        EntityValidator $validator,
        Mailchimp $mailChimp
    ) {
        parent::__construct($entityManager, $repository, $mailChimp);
        $this->validator = $validator;
        $this->mailChimp = $mailChimp;
        $this->listId = $listId;
    }

    public function run(array $member) : CreateMemberTask
    {
        $this->validateResource( $this->listId );

        $this->member = parent::createMember($this->repository, $member);

        $list = $this->getListRepository()->find(  $this->listId  );

        if( $this->errors = $this->validator->hasError( $this->member ) )
            return $this;
        
        try {
            // Save list into db
            $this->saveEntity($this->member);
            // Save list into MailChimp
            $response = $this->mailChimp->post($this->getResourceUrl( $list->getMailChimpId() ), $this->member->toMailChimpArray());
            // Set MailChimp id on the list and save list into db
            $this->saveEntity($this->member->setMailChimpId($response->get('id')));
        } catch (Exception $exception) {
            // Return error response if something goes wrong
            return \response()->json( ['message' => $exception->getMessage()], 400 );
        }

        return $this;
    }
}
