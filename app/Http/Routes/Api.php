<?php
declare(strict_types=1);

/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('/', function(){
    return 'api';
});

// MailChimp group
$router->group(['prefix' => 'mailchimp', 'namespace' => 'MailChimp'], function () use ($router) {
    // Lists group
    $router->group(['prefix' => 'lists'], function () use ($router) {
        $router->post('/', 'ListsController@create');
        $router->get('/', 'ListsController@index');
        $router->get('/{listId}', 'ListsController@show');
        $router->put('/{listId}', 'ListsController@update');
        $router->delete('/{listId}', 'ListsController@remove');

        $router->group(['prefix' => '/{listId}/members'], function() use ($router) {
            $router->post('/', 'ListMembersController@create');
            $router->get('/', 'ListMembersController@getList');
            $router->delete('/{memberId}', 'ListMembersController@remove');
            $router->get('/{memberId}', 'ListMembersController@show');
            $router->put('/{memberId}', 'ListMembersController@update');
        });
    });
});
