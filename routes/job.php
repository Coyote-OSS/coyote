<?php

use Illuminate\Routing\Router;

/** @var $this Router */
$this->group(['namespace' => 'Job', 'prefix' => 'Praca', 'as' => 'job.'], function () {
    /** @var $this Router */
    $this->get('/', ['uses' => 'HomeController@index', 'as' => 'home', 'middleware' => 'json']);

    $this->get('Submit/{job?}', ['uses' => 'SubmitController@index', 'as' => 'submit', 'middleware' => 'auth']);
    $this->post('Submit/{job?}', ['uses' => 'SubmitController@save', 'middleware' => 'auth']);

    $this->get('Tag/Validate', ['uses' => 'TagController@valid', 'as' => 'tag.validate']);
    $this->get('Tag/Suggestions', ['uses' => 'TagController@suggestions', 'as' => 'tag.suggestions']);

    $this->post('Delete/{job}', ['uses' => 'DeleteController@index', 'as' => 'delete']);

    $this->get('Technologia/{name}', ['uses' => 'HomeController@tag', 'as' => 'tag', 'middleware' => 'json']);
    $this->get('Zdalna', ['uses' => 'HomeController@remote', 'as' => 'remote', 'middleware' => 'json']);
    $this->get('Miasto/{name}', ['uses' => 'HomeController@city', 'as' => 'city', 'middleware' => 'json']);
    $this->get('Firma/{name}', ['uses' => 'HomeController@firm', 'as' => 'firm', 'middleware' => 'json']);
    $this->get('Moje', ['uses' => 'MineController@index', 'as' => 'mine', 'middleware' => ['auth', 'json']]);

    $this->get('{job}-{slug}', ['uses' => 'OfferController@index', 'as' => 'offer', 'middleware' => 'page.hit']);

    $this->post('Subscribe/{job}', [
        'uses'       => 'SubscribeController@index',
        'as'         => 'subscribe',
        'middleware' => 'auth',
    ]);

    $this->post('Preferences', ['uses' => 'PreferencesController@index', 'as' => 'preferences']);

    // Send job's application
    // ------------------------------------------------------------------------------------------
    $this->get('Application/{job}', ['uses' => 'ApplicationController@submit', 'as' => 'application']);
    $this->post('Application/{job}', ['uses' => 'ApplicationController@save']);
    $this->post('Upload', ['uses' => 'ApplicationController@upload', 'as' => 'application.upload']);
    $this->get('Application/{job}/{id}', ['uses' => 'ApplicationController@downloadApplication', 'as' => 'application.download']);

    // Payment routes
    // -----------------------------
    $this->any('Payment/Status', [
        'uses' => 'PaymentController@paymentStatus',
        'as'   => 'payment.status',
    ]);

    $this->get('Payment/{payment}', [
        'uses'       => 'PaymentController@index',
        'as'         => 'payment',
        'middleware' => 'auth',
    ]);

    $this->post('Payment/{payment}', ['uses' => 'PaymentController@makePayment', 'middleware' => 'auth']);

    $this->get('Payment/{payment}/Success', [
        'uses' => 'PaymentController@success',
        'as'   => 'payment.success',
    ]);

    $this->get('Coupon/Validate', ['uses' => 'CouponController@validateCode', 'as' => 'coupon']);
    $this->get('Renew/{job}', ['uses' => 'SubmitController@renew', 'as' => 'renew', 'middleware' => 'auth']);

    $this->get('Oferta', ['uses' => 'BusinessController@show', 'as' => 'business']);

    // Job's ads
    // --------------------------------------------------------------
    $this->get('recommendations', ['uses' => 'AdController@index', 'as' => 'ad']);
    $this->get('fb', ['uses' => 'FbController@showByCategory']);
    $this->get('fb_keyword', ['uses' => 'FbController@showByKeyword']);
});

$this->group(['namespace' => 'Firm', 'prefix' => 'Firma', 'as' => 'firm.'], function () {
    $this->post('Logo', ['uses' => 'SubmitController@logo', 'as' => 'logo']);
});
