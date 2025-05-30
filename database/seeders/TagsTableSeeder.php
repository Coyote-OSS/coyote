<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TagsTableSeeder extends Seeder {
    public function run(): void {
        $this->createTag('c#', 'C#');
        $this->createTag('c', 'C');
        $this->createTag('c++', 'C++');
        $this->createTag('delphi', 'Delphi');
        $this->createTag('docker', 'Docker');
        $this->createTag('java', 'Java');
        $this->createTag('pascal', 'Pascal');
        $this->createTag('php', 'PHP');
        $this->createTag('python', 'Python');
        $this->createTag('react', 'React');
        $this->createTag('ruby', 'Ruby');
        $this->createTag('vba', 'VisualBasic for Applications');
        $this->createTag('vue', 'Vue');
    }

    private function createTag(string $name, ?string $title = null): void {
        \Coyote\Tag::query()->create([
            'name'      => $name,
            'real_name' => $title,
        ]);
    }
}
