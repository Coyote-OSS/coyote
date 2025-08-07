<?php
namespace Database\Seeders;

use Coyote\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class UserAvatarsSeeder extends Seeder
{
    private array $userAvatars = [
        'database/seeders/userAvatars/ball.png',
        'database/seeders/userAvatars/cats.png',
        'database/seeders/userAvatars/face.png',
        'database/seeders/userAvatars/film.png',
        'database/seeders/userAvatars/john.png',
        'database/seeders/userAvatars/knut.png',
        'database/seeders/userAvatars/moon.png',
        'database/seeders/userAvatars/paul.png',
        'database/seeders/userAvatars/wave.png',
        'database/seeders/userAvatars/wolf.png',
    ];

    public function run(): void
    {
        foreach (User::query()->get() as $index => $user) {
            $group = $index % 10;
            if ($group < 3) {
                continue;
            }
            $user->photo->uploadFile($user->name, $this->newUserAvatar());
            $user->save();
        }
    }

    private function newUserAvatar(): string
    {
        return Arr::random($this->userAvatars, null);
    }
}
