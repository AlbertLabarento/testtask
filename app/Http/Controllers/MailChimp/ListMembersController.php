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
use App\Containers\Member\UpdateMemberTask;
use App\Containers\Services\EntityValidator;

class ListMembersController extends Controller
{
    /**
     * @var \Mailchimp\Mailchimp
     */
    private $mailChimp;

    /**
     * ListsController constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Mailchimp\Mailchimp $mailchimp
     */
    public function __construct(EntityManagerInterface $entityManager, Mailchimp $mailchimp)
    {
        parent::__construct($entityManager);

        $this->mailChimp = $mailchimp;
    }

    public function getList(Request $request, string $listId ) : JsonResponse
    {
        $getMembersTask = $this->execute( GetMembersTask::class, [ 'listId' => $listId ] )->run();
        return $this->successfulListResponse( $getMembersTask );
    }

    public function create(Request $request, string $listId) : JsonResponse
    {
        $member = $this->execute( CreateMemberTask::class, [ 'listId' => $listId ] )->run( $request->all() );
        
        if( $errors = $member->hasErrors() )
            return $this->errorResponse( $errors );

        return $this->successfulResponse($member->getMemberEntity()->toArray());

    }

    public function remove(string $listId, string $memberId) : JsonResponse
    {
        $member = $this->execute( DeleteMemberTask::class, [ 'listId' => $listId, 'memberId' => $memberId ] )->run();

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
        // $member = $this->entityManager->getRepository(MailChimpListMember::class)->find($memberId);
        $member = $this->execute(   FindMemberTask::class, [ 'listId' => $listId ] )->run( $memberId );
        
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
            $updateMember = $this->execute( UpdateMemberTask::class, [ 'listId' => $listId, 'memberId' => $memberId ] )->run( $request->all() );
        
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
