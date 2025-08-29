<?php

// Obsluga mikroblogow
/** @var $this \Illuminate\Routing\Router */
$this->group(['namespace' => 'Microblog', 'prefix' => 'Mikroblogi', 'as' => 'microblog.'], function () {
    $this->get('/', ['uses' => 'HomeController@index', 'as' => 'home']);
    $this->post('Edit/{microblog?}', ['uses' => 'SubmitController@save', 'as' => 'save', 'middleware' => ['auth', 'throttle.submission:1,1']]);
    $this->post('Preview', ['uses' => 'SubmitController@preview', 'middleware' => 'auth']);

    $this->get('View/{id}', ['uses' => 'HomeController@show', 'as' => 'view']);
    $this->post('Vote/{microblog}', ['uses' => 'VoteController@post', 'as' => 'vote']);
    $this->get('Voters/{any_microblog}', ['uses' => 'VoteController@voters']);
    $this->delete('Delete/{microblog}', ['uses' => 'SubmitController@delete', 'as' => 'delete', 'middleware' => 'auth']);
    $this->post('Restore/{id}', ['uses' => 'SubmitController@restore', 'as' => 'restore', 'middleware' => 'auth']);
    $this->post('Sponsored/{microblog}', ['uses' => 'SubmitController@toggleSponsored', 'middleware' => 'auth']);

    // edycja/publikacja komentarza oraz jego usuniecie
    $this->post('Comment/{microblog?}', ['uses' => 'CommentController@save', 'as' => 'comment.save', 'middleware' => ['auth', 'throttle.submission:1,1']]);
    $this->delete('Comment/Delete/{microblog}', ['uses' => 'CommentController@delete', 'as' => 'comment.delete', 'middleware' => 'auth']);
    // pokaz reszte komentarzy...
    $this->get('Comment/Show/{id}', ['uses' => 'CommentController@show', 'as' => 'comment.show']);

    $this->post('Hit/{any_microblog}', ['uses' => 'HitController@index', 'middleware' => 'page.hit']);
    $this->get('{tag_name}', ['uses' => 'HomeController@tag', 'as' => 'tag']);
});

// this route refers to a different namespace than above ones
$this->post('Mikroblogi/Subscribe/{any_microblog}', ['uses' => 'SubscribeController@microblog', 'middleware' => 'auth']);
