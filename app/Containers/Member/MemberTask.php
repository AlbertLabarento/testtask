<?php 

namespace App\Containers\Member;

use App\Database\Entities\Entity;

abstract class MemberTask 
{
    protected  $resourceName;

    protected $entityManager;

    protected $repository;

    public $errors = [];

    protected $listId;

    protected $member;

    public function __construct(
        $resourceName,
        \Doctrine\ORM\EntityManagerInterface $entityManager,
        Entity $repository
    )
    {
        $this->resourceName = $resourceName;
        $this->entityManager = $entityManager;
        $this->repository = $repository;
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
}