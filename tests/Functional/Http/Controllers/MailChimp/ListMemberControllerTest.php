<?php
declare(strict_types=1);

namespace Tests\App\Functional\Http\Controllers\MailChimp;

use Tests\App\TestCases\MailChimp\ListMemberTestCase;
use Tests\App\TestCases\MailChimp\ListTestCase;

class ListMemberControllerTest extends ListMemberTestCase
{
    /**
     * Test application creates successfully list and returns it back with id from MailChimp.
     *
     * @return void
     */
    public function testCreateListMemberSuccessfully(): void
    {
        $listId = $this->getListId();
        static::$memberData['list_id'] = $listId;
        $this->post("/mailchimp/lists/$listId/members", static::$memberData);

        $content = \json_decode($this->response->getContent(), true);

        $this->assertResponseOk();
        $this->seeJson(static::$memberData);
        self::assertArrayHasKey('mail_chimp_id', $content);
        self::assertNotNull($content['mail_chimp_id']);

        $this->createdMemberListIds[$listId] = $content['mail_chimp_id']; 
        $this->createdListIds[] = $listId; 
    }

    /**
     * Test application returns error response with errors when list validation fails.
     *
     * @return void
     */
    public function testCreateListValidationFailed(): void
    {
        $listId = $this->getListId();
        $this->post("/mailchimp/lists/$listId/members");

        $content = \json_decode($this->response->getContent(), true);

        $this->assertResponseStatus(400);
        self::assertArrayHasKey('message', $content);
        self::assertArrayHasKey('errors', $content);
        self::assertEquals('Invalid data given', $content['message']);

        foreach (\array_keys(static::$memberData) as $key) {
            if (\in_array($key, static::$notRequired, true)) {
                continue;
            }

            self::assertArrayHasKey($key, $content['errors']);
        }

        $this->createdListIds[] = $listId; 
    }

    /**
     * Test application returns empty successful response when removing existing list.
     *
     * @return void
     */
    public function testRemoveMemberSuccessfully(): void
    {
        $listId = $this->getListId();
        $this->post("/mailchimp/lists/$listId/members", [
            'email_address' => 'oliverqueen@gmail.com',
            'status' => 'subscribed' ,
            'listId' => $listId   
        ]);

        $content = \json_decode($this->response->getContent(), true);
        $memberId = $content['member_id'];
        $list = \json_decode($this->response->content(), true);

        $this->delete("/mailchimp/lists/$listId/members/$memberId");
        $this->assertResponseOk();
        self::assertEmpty(\json_decode($this->response->content(), true));

        $this->createdListIds[] = $listId; 
    }

    /**
     * Test application returns error response when list not found.
     *
     * @return void
     */
    public function testShowMembersNotFoundException(): void
    {
        $listId = $this->getListId();
        $this->get("/mailchimp/lists/$listId/members/invalid-member-id");

        $this->assertListMemberNotFoundResponse('invalid-member-id');
        $this->createdListIds[] = $listId; 
    }

    /**
     * Test application returns successful response with list data when requesting existing list.
     *
     * @return void
     */
    public function testShowMemberSuccessfully(): void
    {
        $list = $this->createList(static::$listData);
        $memberData = static::$memberData;
        $memberData['list_id'] = $list->getId();
        $member = $this->createMember($memberData);

        $this->get(\sprintf( '/mailchimp/lists/%s/members/%s', $list->getId(), $member->getId() ) );
        $content = \json_decode($this->response->content(), true);

        $this->assertResponseOk();

        foreach ($memberData as $key => $value) {
            self::assertArrayHasKey($key, $content);
            self::assertEquals($value, $content[$key]);
        }
    }

     /**
     * Test application returns error response when list not found.
     *
     * @return void
     */
    public function testUpdateListNotFoundException(): void
    {
        $listId = $this->getListId();
        $this->put("/mailchimp/lists/$listId/members/invalid-member-id");
        $this->assertListMemberNotFoundResponse('invalid-member-id');
        $this->createdListIds[] = $listId; 
    }

    /**
     * Test application returns successfully response when updating existing list with updated values.
     *
     * @return void
     */
    public function testUpdateListSuccessfully(): void
    {
        $listId = $this->getListId();
        $this->post("/mailchimp/lists/$listId/members", [
            'email_address' => 'johndiggle@gmail.com',
            'status' => 'subscribed' ,
            'listId' => $listId   
        ]);

        $memberId = \json_decode($this->response->getContent(), true)['member_id'];
        $this->put("/mailchimp/lists/$listId/members/$memberId", ['status' => 'subscribed']);
        $content = \json_decode($this->response->content(), true);
        $this->assertResponseOk();
        foreach (\array_keys(static::$memberData) as $key) {
            self::assertArrayHasKey($key, $content);
            self::assertEquals('subscribed', $content['status']);
        }

        $this->createdMemberListIds[$listId] = $content['mail_chimp_id'];  
        $this->createdListIds[] = $listId;
    }

     /**
     * Test application returns error response with errors when list validation fails.
     *
     * @return void
     */
    public function testUpdateMemberValidationFailed(): void
    {
        $listId = $this->getListId();
        $this->post("/mailchimp/lists/$listId/members", [
            'email_address' => 'westallen@gmail.com',
            'status' => 'subscribed' ,
            'listId' => $listId   
        ]);

        $memberId = \json_decode($this->response->getContent(), true)['member_id'];

        $this->put(\sprintf('/mailchimp/lists/%s/members/%s', $listId, $memberId), ['status' => 'invalid']);
        $content = \json_decode($this->response->content(), true);

        $this->assertResponseStatus(400);
        $message = $content['message'];
        self::assertArrayHasKey('type', $message);
        self::assertArrayHasKey('title', $message);
        self::assertArrayHasKey('status', $message);
        self::assertArrayHasKey('detail', $message);
        self::assertArrayHasKey('instance', $message);
        self::assertEquals('Invalid status given: invalid', $message['detail']);

        $this->createdMemberListIds[$listId] = $memberId;  
        $this->createdListIds[] = $listId;
    }

}
