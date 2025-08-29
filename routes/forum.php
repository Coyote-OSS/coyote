<?php

use Illuminate\Routing\Router;

// sciaganie zalacznika (stara regula routingu
$this->get('Forum/Download/{asset}', ['uses' => 'AssetsController@download', 'as' => 'download']);

/** @var $this Router */
$this->group(['namespace' => 'Forum', 'prefix' => 'Forum', 'as' => 'forum.'], function () {
    /** @var $this Router */
    $this->get('/', ['uses' => 'HomeController@index', 'as' => 'home']);

    $this->post('Preview', ['uses' => 'SubmitController@preview', 'as' => 'preview']);

    $this->get('Tag/{tag_name}', ['uses' => 'HomeController@tag', 'as' => 'tag']);
    $this->post('Tag/Save', ['uses' => 'TagController@save', 'as' => 'tag.save']);
    $this->post('Tag/Validation', ['uses' => 'TagController@validation', 'as' => 'tag.validate']);
    $this->get('Categories', ['uses' => 'HomeController@categories', 'as' => 'categories']);
    $this->get('All', ['uses' => 'HomeController@all', 'as' => 'all']);
    $this->get('Mine', ['uses' => 'HomeController@mine', 'as' => 'mine', 'middleware' => 'auth']);
    $this->get('Subscribes', ['uses' => 'HomeController@subscribes', 'as' => 'subscribes', 'middleware' => 'auth']);
    $this->get('User/{id}', ['uses' => 'HomeController@user', 'as' => 'user']);
    $this->get('Interesting', ['uses' => 'HomeController@interesting', 'as' => 'interesting']);
    $this->post('Mark', ['uses' => 'HomeController@mark', 'as' => 'mark']);

    // Add or edit topic's post
    // ----------------------------------------------------
    $this->get('{forum}/Submit/{topic}/{post?}', [
        'uses'       => 'SubmitController@index',
        'as'         => 'post.submit',
        'middleware' => ['topic.access', 'can:access,forum', 'forum.write'],
    ]);

    $this->post('{forum}/Submit/{topic}/{post?}', [
        'uses'       => 'SubmitController@save',
        'as'         => 'topic.save',
        'middleware' => ['topic.access', 'can:access,forum', 'forum.write', 'throttle.submission:1,1'],
    ]);

    // Add new topic
    // -------------------------------------------------
    $this->get('{forum}/Submit/{topic?}', [
        'uses'       => 'SubmitController@index',
        'as'         => 'topic.submit',
        'middleware' => ['can:access,forum', 'forum.write', 'forum.url'],
    ]);

    $this->post('{forum}/Submit/{topic?}', [
        'uses'       => 'SubmitController@save',
        'middleware' => ['can:access,forum', 'forum.write', 'forum.url', 'throttle.submission:1,1'],
    ]);

    // Change topic's title
    // ----------------------------------------------
    $this->post('Topic/Subject/{topic}', [
        'uses'       => 'SubmitController@subject',
        'as'         => 'topic.subject',
        'middleware' => 'auth',
    ]);

    $this->post('{forum}/Mark', [
        'uses'       => 'CategoryController@mark',
        'as'         => 'category.mark',
        'middleware' => 'can:access,forum',
    ]);

    // blokowanie watku
    $this->post('Topic/Lock/{topic}', ['uses' => 'LockController@index', 'as' => 'lock', 'middleware' => 'auth']);
    // przeniesienie watku do innej kategorii
    $this->post('Topic/Move/{topic}', ['uses' => 'MoveController@index', 'as' => 'move']);
    // oznacz watek jako przeczytany
    $this->post('Topic/Mark/{topic}', ['uses' => 'TopicController@mark', 'as' => 'topic.mark']);

    // dziennik zdarzen dla watku
    $this->get('Stream/{topic_trashed}', ['uses' => 'StreamController@index', 'as' => 'stream', 'middleware' => ['auth']]);

    // widok kategorii forum
    $this->get('{forum}', [
        'uses'       => 'CategoryController@index',
        'as'         => 'category',
        'middleware' => ['can:access,forum', 'forum.url'],
    ]);

    // usuwanie posta
    $this->delete('Post/Delete/{post}', [
        'uses'       => 'DeleteController@index',
        'as'         => 'post.delete',
        'middleware' => 'auth',
    ]);

    // przywracanie posta
    $this->post('Post/Restore/{id}', [
        'uses'       => 'RestoreController@index',
        'as'         => 'post.restore',
        'middleware' => 'auth',
    ]);

    // glosowanie na dany post
    $this->post('Post/Vote/{post}', ['uses' => 'VoteController@index', 'as' => 'post.vote']);
    $this->get('Post/Voters/{post}', ['uses' => 'VoteController@voters']);
    // akceptowanie danego posta jako poprawna odpowiedz w watku
    $this->post('Post/Accept/{post}', ['uses' => 'AcceptController@index', 'as' => 'post.accept', 'middleware' => 'auth']);
    // historia edycji danego posta
    $this->get('Post/Log/{id}', ['uses' => 'LogController@log', 'as' => 'post.log']);
    // przywrocenie poprzedniej wersji posta
    $this->post('Post/Rollback/{post}/{id}', ['uses' => 'RollbackController@rollback', 'as' => 'post.rollback']);
    // mergowanie posta z poprzednim
    $this->post('Post/Merge/{post}', ['uses' => 'MergeController@index', 'as' => 'post.merge']);
    $this->get('Post/{post}', ['uses' => 'PostController@show']);

    // edycja/publikacja komentarza oraz jego usuniecie
    $this->post('Comment/{comment?}', [
        'uses'       => 'CommentController@save',
        'as'         => 'comment.save',
        'middleware' => ['auth', 'throttle.submission:1,1'],
    ]);

    $this->delete('Comment/Delete/{comment}', [
        'uses'       => 'CommentController@delete',
        'as'         => 'comment.delete',
        'middleware' => ['auth'],
    ]);

    $this->get('Comment/Show/{post}', [
        'uses' => 'CommentController@getAll',
        'as'   => 'comment.show',
    ]);

    $this->post('Comment/Migrate/{comment}', [
        'uses'       => 'CommentController@migrate',
        'as'         => 'comment.migrate',
        'middleware' => ['auth'],
    ]);

    $this->get('Comment/{comment}', [
        'uses'       => 'CommentController@show',
        'middleware' => ['auth'],
    ]);

    // glosowanie w ankiecie
    $this->post('Poll/{poll}', [
        'uses'       => 'PollController@vote',
        'as'         => 'poll.vote',
        'middleware' => ['auth'],
    ]);

    // change category order
    $this->post('Setup', ['uses' => 'CategoryController@setup', 'middleware' => 'auth']);
    $this->post('{forum}/Collapse', ['uses' => 'CategoryController@collapseSection', 'as' => 'section']);

    // Show topic
    // -------------------------------------------------------
    $this->get('{forum}/{topic}-{slug?}', [
        'uses'       => 'TopicController@index',
        'as'         => 'topic',
        'middleware' => ['topic.access', 'can:access,forum', 'topic.scroll', 'page.hit', 'json'],
    ]);

    // skrocony link do posta
    $this->get('{id}', ['uses' => 'ShareController@index', 'as' => 'share']);
});

// obserwowanie danego watku na forum
$this->post('Forum/Topic/Subscribe/{topic}', [
    'uses'       => 'SubscribeController@topic',
    'as'         => 'topic.subscribe',
    'middleware' => 'auth',
]);

// obserwowanie posta
$this->post('Forum/Post/Subscribe/{post}', [
    'uses'       => 'SubscribeController@post',
    'as'         => 'post.subscribe',
    'middleware' => 'auth',
]);
