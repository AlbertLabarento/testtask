<?php 

namespace App\Containers\Member;

use App\Database\Entities\Entity;
use App\Database\Entities\MailChimp\MailChimpList;

abstract class MemberTask 
{
    protected  $resourceName;

    protected $entityManager;

    protected $repository;

    public $errors = [];

    protected $listId;

    protected $memberId;

    protected $member;

    protected $mailChimp;

    public function __construct(
        \Doctrine\ORM\EntityManagerInterface $entityManager,
        \App\Database\Entities\MailChimp\MailChimpListMember $repository,
        \Mailchimp\Mailchimp $mailChimp
    )
    {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->mailChimp = $mailChimp;
    }

    public function getList() : array
    {
        return $this->getRepository()->findAll();
    }

    public function createMember( Entity $repository, $member ) : Entity
    {
        return new $repository( $member );
    }

    public function getMember( string $id )
    {
        return  $this->getRepository()->find($id);
    }

    private function getRepository() : \Doctrine\ORM\EntityRepository
    {
        return $this->entityManager->getRepository( get_class( $this->repository ) );
    }

    /**
     * Remove entity from database.
     *
     * @param \App\Database\Entities\Entity $entity
     *
     * @return void
     */
    protected function removeEntity(Entity $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }

    /**
     * Save entity into database.
     *
     * @param \App\Database\Entities\Entity $entity
     *
     * @return void
     */
    protected function saveEntity(Entity $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    public function hasErrors() 
    {
        return $this->errors;
    }

    public function getMemberEntity()
    {
        return $this->member;
    }

    protected function validateResource( string $listId, string $memberId = null )
    {
        if( is_null( $this->getListRepository()->find( $listId ) ) )
            throw new \App\Containers\Exceptions\ListNotFoundException( $listId );
        
        if( $memberId )
            if(  is_null( $this->getRepository( MailChimpList::class )->find( $memberId ) ) )
                throw new \App\Containers\Exceptions\MemberNotFoundException( $memberId );
    }

    public function getListRepository() : \Doctrine\ORM\EntityRepository 
    {
        return $this->entityManager->getRepository( MailChimpList::class );
    }

    public function validateList()
    {

    }

    public function validateMember()
    {

    }

    public function getResourceUrl( string $listId, string $memberId = null ) : string
    {
        return \sprintf('lists/%s/members/%s', $listId, $memberId);
    }
}