<?php

namespace Coyote\Http\Controllers;

use Coyote\Events\TopicSaved;
use Coyote\Forum;
use Coyote\Guide;
use Coyote\Microblog;
use Coyote\Post;
use Coyote\Topic;

class SubscribeController extends Controller
{
    public function forum(Forum $forum)
    {
        return $this->toggle($forum);
    }

    public function guide(Guide $guide)
    {
        return $this->toggle($guide);
    }

    public function microblog(Microblog $microblog)
    {
        return $this->toggle($microblog);
    }

    public function post(Post $post)
    {
        return $this->toggle($post);
    }

    public function topic(Topic $topic)
    {
        $count = $this->toggle($topic);

        event(new TopicSaved($topic));

        return $count;
    }

    private function toggle(Forum | Guide | Microblog | Topic | Post $model)
    {
        $subscriber = $model->subscribers()->forUser($this->userId)->first();

        if ($subscriber) {
            $subscriber->delete();
        } else {
            $model->subscribers()->create(['user_id' => $this->userId]);
        }

        return response($model->subscribers()->count());
    }
}
