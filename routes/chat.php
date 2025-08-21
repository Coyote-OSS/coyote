<?php

$this->get('Chat', ['uses' => 'Chat\ChatController@index']);
$this->post('Chat/Message', ['uses' => 'Chat\ChatController@sendMessage']);
