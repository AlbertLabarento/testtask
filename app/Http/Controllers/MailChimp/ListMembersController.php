<?php
declare(strict_types=1);

namespace App\Http\Controllers\MailChimp;

use App\Database\Entities\MailChimp\MailChimpListMember;
use App\Http\Controllers\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mailchimp\Mailchimp;
use App\Containers\Member\GetMembersTask;
use App\Containers\Member\CreateMemberTask;
use App\Containers\Member\DeleteMemberTask;
use App\Containers\Member\FindMemberTask;
use App\Containers\Member\FindMemberRevised;
use App\Containers\Member\UpdateMemberTask;
use App\Containers\Services\EntityValidator;

class ListMembersController extends Controller
{
    /**
     * @var \Mailchimp\Mailchimp
     */
    private $mailChimp;

    private $mailChimpMember;

    private $entityValidator;

    /**
     * ListsController constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Mailchimp\Mailchimp $mailchimp
     */
    public function __construct(
        EntityManagerInterface $entityManager, 
        Mailchimp $mailChimp, 
        \App\Database\Entities\MailChimp\MailChimpListMember $mailChimpMember,
        \App\Containers\Services\EntityValidator $entityValidator)
    {
        parent::__construct($entityManager);

        $this->mailChimp = $mailChimp;
        $this->mailChimpMember = $mailChimpMember;
        $this->entityValidator = $entityValidator;
    }

    public function getList(Request $request, string $listId ) : JsonResponse
    {
        $getMembersTask = ( new GetMembersTask( $listId, $this->entityManager, $this->mailChimpMember ) )->run();
        return $this->successfulListResponse( $getMembersTask );
    }

    public function create(Request $request, string $listId) : JsonResponse
    {
        $member = ( new CreateMemberTask( $listId, $this->entityManager, $this->mailChimpMember, $this->entityValidator, $this->mailChimp ) )->run( $request->all() );
        
        if( $errors = $member->hasErrors() )
            return $this->errorResponse( $errors );

        return $this->successfulResponse($member->getMemberEntity()->toArray());

    }

    public function remove(string $listId, string $memberId) : JsonResponse
    {
        $member = ( new DeleteMemberTask( $listId, $memberId, $this->entityManager, $this->mailChimpMember, $this->mailChimp ) )->run();

        if( $errors = $member->hasErrors() )
            return $this->errorResponse( $errors );

        return $this->successfulResponse([]);
    }

    /**
     * Retrieve and return MailChimp member.
     *
     * @param string $listId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $listId, string $memberId): JsonResponse
    {
        /** @var \App\Database\Entities\MailChimp\MailChimpListMember|null $member */
        $member = ( new FindMemberTask( $listId, $this->entityManager, $this->mailChimpMember ) )->run($memberId);
        
        if ($errors = $member->hasErrors() ) {
            return $this->errorResponse(
                $errors,
                404
            );
        }

        return $this->successfulResponse($member->getMemberEntity()->toArray());
    }

    /**
     * Update MailChimp meber.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $listId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $listId, string $memberId ): JsonResponse
    {
        /** @var \App\Database\Entities\MailChimp\MailChimpListMember|null $list */
        
        try {
            $updateMember = ( new UpdateMemberTask( $listId, $memberId, $this->entityManager, $this->mailChimpMember, $this->mailChimp, $this->entityValidator ) )->run( $request->all() );
        
            if ($errors = $updateMember->hasErrors() ) {
                return $this->errorResponse(
                    $errors,
                    404
                );
            }
        } catch (Exception $exception) {
            return $this->errorResponse(['message' => json_decode($exception->getMessage(), true)]);
        }

        return $this->successfulResponse($updateMember->getMemberEntity()->toArray());
    }
}
