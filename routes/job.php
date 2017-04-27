<?php

/*
 * Modul "Praca"
 */
/** @var $this \Illuminate\Routing\Router */
$this->group(['namespace' => 'Job', 'prefix' => 'Praca', 'as' => 'job.'], function () {
    $this->get('/', ['uses' => 'HomeController@index', 'as' => 'home', 'middleware' => 'job.redirect']);
    $this->get('My', ['uses' => 'MyOffersController@index', 'as' => 'my', 'middleware' => 'auth']);

    $this->get('Submit/{id?}', ['uses' => 'SubmitController@getIndex', 'as' => 'submit', 'middleware' => 'auth']);
    $this->post('Submit', ['uses' => 'SubmitController@postIndex', 'middleware' => 'auth']);

    $this->get('Submit/Firm', ['uses' => 'SubmitController@getFirm', 'as' => 'submit.firm', 'middleware' => 'auth']);
    $this->post('Submit/Firm', ['uses' => 'SubmitController@postFirm', 'middleware' => 'auth']);

    $this->get('Submit/Preview', [
        'uses' => 'SubmitController@getPreview',
        'as' => 'submit.preview',
        'middleware' => 'auth'
    ]);

    $this->post('Submit/Save', ['uses' => 'SubmitController@save', 'as' => 'submit.save', 'middleware' => 'auth']);

    $this->post('Tag/Submit', ['uses' => 'TagController@submit', 'as' => 'submit.tag']);
    $this->get('Tag/Prompt', ['uses' => 'TagController@prompt', 'as' => 'tag.prompt']);
    $this->get('Tag/Validate', ['uses' => 'TagController@valid', 'as' => 'tag.validate']);
    $this->get('Tag/Suggestions', ['uses' => 'TagController@suggestions', 'as' => 'tag.suggestions']);

    $this->post('Delete/{job}', ['uses' => 'DeleteController@index', 'as' => 'delete']);

    $this->get('Technologia/{name}', ['uses' => 'HomeController@tag', 'as' => 'tag', 'middleware' => 'job.redirect']);
    $this->get('Zdalna', ['uses' => 'HomeController@remote', 'as' => 'remote', 'middleware' => 'job.redirect']);
    $this->get('Miasto/{name}', ['uses' => 'HomeController@city', 'as' => 'city', 'middleware' => 'job.redirect']);
    $this->get('Firma/{name}', ['uses' => 'HomeController@firm', 'as' => 'firm', 'middleware' => 'job.redirect']);

    $this->get('{job}-{slug}', ['uses' => 'OfferController@index', 'as' => 'offer']);

    $this->post('Subscribe/{job}', [
        'uses' => 'SubscribeController@index',
        'as' => 'subscribe',
        'middleware' => 'auth'
    ]);

    $this->post('Preferences', ['uses' => 'PreferencesController@index', 'as' => 'preferences']);

    // Send job's application
    // ------------------------------------------------------------------------------------------
    $this->get('Application/{job}', ['uses' => 'ApplicationController@submit', 'as' => 'application']);
    $this->post('Application/{job}', ['uses' => 'ApplicationController@save', 'as' => 'application']);
    $this->post('Upload', ['uses' => 'ApplicationController@upload', 'as' => 'application.upload']);

    // move job offer
    $this->get('Move/{job}', ['uses' => 'MoveController@index', 'as' => 'move', 'middleware' => 'can:job-delete']);
    $this->post('Move/{job}', ['uses' => 'MoveController@move', 'middleware' => 'can:job-delete']);

    // Payment routes
    // -----------------------------
    $this->get('Payment/{payment}', [
        'uses' => 'PaymentController@index',
        'as' => 'payment',
        'middleware' => 'auth'
    ]);

    $this->post('Payment/{payment}', ['uses' => 'PaymentController@process', 'middleware' => 'auth']);

    $this->post('Payment/{payment}/Callback', [
        'uses' => 'PaymentController@callback',
        'as' => 'payment.callback',
        'middleware' => 'auth'
    ]);

    // Job's ads
    // --------------------------------------------------------------
    $this->get('recommendations', ['uses' => 'AdController@index', 'as' => 'ad']);
});

$this->group(['namespace' => 'Firm', 'prefix' => 'Firma', 'as' => 'firm.'], function () {
    $this->post('Logo', ['uses' => 'SubmitController@logo', 'as' => 'logo']);
});
