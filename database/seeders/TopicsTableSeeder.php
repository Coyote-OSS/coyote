<?php
namespace Database\Seeders;

use Coyote\Forum;
use Coyote\Post;
use Coyote\Post\Log;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Database\Seeder;

class TopicsTableSeeder extends Seeder {
    /**
     * Fixed set of topics with known titles, forums, authors and content, so that tests can rely on
     * deterministic seed data instead of Faker-generated randomness.
     *
     * @var array<int, array{title: string, forum: string, posts: array<int, array{user: string, text: string}>}>
     */
    private const TOPICS = [
        [
            'title' => 'How to start learning PHP in 2024?',
            'forum' => 'PHP',
            'posts' => [
                ['user' => 'user', 'text' => "I'm planning to learn PHP from scratch. What resources would you recommend for a beginner?"],
                ['user' => 'acceptance-test-1', 'text' => 'Start with the official PHP documentation, then build a small project like a simple blog.'],
                ['user' => 'acceptance-test-2', 'text' => "Laracasts is also great once you're ready to move on to Laravel."],
            ],
        ],
        [
            'title' => 'Best practices for React hooks',
            'forum' => 'JavaScript',
            'posts' => [
                ['user' => 'user', 'text' => 'What are the common pitfalls when using useEffect?'],
                ['user' => 'acceptance-test-3', 'text' => 'Watch out for missing dependencies in the dependency array — the ESLint plugin catches most of them.'],
            ],
        ],
        [
            'title' => 'Python vs Go for backend services',
            'forum' => 'Python',
            'posts' => [
                ['user' => 'user', 'text' => 'We are choosing a language for a new microservice. Python or Go?'],
                ['user' => 'acceptance-test-4', 'text' => 'Go if you care about raw performance and static typing, Python if you want faster iteration.'],
                ['user' => 'acceptance-test-5', 'text' => 'Depends on the team — Python is easier to onboard new developers into.'],
            ],
        ],
        [
            'title' => 'Docker Compose setup for local development',
            'forum' => 'Devops',
            'posts' => [
                ['user' => 'user', 'text' => 'Sharing my docker-compose.yml for a Laravel + Postgres + Redis stack, feedback welcome.'],
                ['user' => 'acceptance-test-6', 'text' => 'Looks solid, consider adding healthchecks for the database service.'],
            ],
        ],
        [
            'title' => 'How to prepare for a technical interview',
            'forum' => 'Kariera',
            'posts' => [
                ['user' => 'user', 'text' => 'I have an interview next week for a mid-level developer position. Any tips?'],
                ['user' => 'acceptance-test-7', 'text' => 'Review data structures and algorithms, and be ready to talk through your past projects in detail.'],
                ['user' => 'acceptance-test-8', 'text' => 'Practice explaining your reasoning out loud, not just solving the problem.'],
            ],
        ],
        [
            'title' => 'Weekend project: building a CLI todo app',
            'forum' => 'Off-Topic',
            'posts' => [
                ['user' => 'user', 'text' => 'Built a small CLI todo app this weekend, was a fun way to try a new language.'],
                ['user' => 'acceptance-test-9', 'text' => 'Nice, what language did you use?'],
                ['user' => 'user', 'text' => 'Rust, still getting used to the borrow checker.'],
            ],
        ],
        [
            'title' => 'Choosing between SQL and NoSQL for a new project',
            'forum' => 'Bazy_danych',
            'posts' => [
                ['user' => 'user', 'text' => 'Starting a new project with flexible, evolving data — SQL or NoSQL?'],
                ['user' => 'acceptance-test-10', 'text' => 'If your data has clear relationships, stick with SQL — schema flexibility rarely outweighs consistency guarantees.'],
            ],
        ],
        [
            'title' => 'Algorithms every developer should know',
            'forum' => 'Algorytmy',
            'posts' => [
                ['user' => 'user', 'text' => 'What are the must-know algorithms for day-to-day development, not just interviews?'],
                ['user' => 'acceptance-test-122', 'text' => 'Binary search, sorting basics, and graph traversal (BFS/DFS) cover most real-world cases.'],
                ['user' => 'acceptance-test-123', 'text' => "I'd add hashing and basic dynamic programming to that list."],
            ],
        ],
    ];

    public function run(): void {
        Log::reguard();
        foreach (self::TOPICS as $definition) {
            $this->seedTopic($definition);
        }
    }

    private function seedTopic(array $definition): void {
        $forum = Forum::query()->where('slug', $definition['forum'])->firstOrFail();

        $topic = new Topic(['title' => $definition['title'], 'forum_id' => $forum->id]);
        $topic->save();

        foreach ($definition['posts'] as $postDefinition) {
            $this->seedPost($topic, $postDefinition);
        }
    }

    private function seedPost(Topic $topic, array $postDefinition): void {
        $post = new Post([
            'forum_id' => $topic->forum_id,
            'user_id'  => $this->user($postDefinition['user'])->id,
            'text'     => $postDefinition['text'],
            'ip'       => '127.0.0.1',
            'browser'  => 'Mozilla/5.0',
        ]);

        $topic->posts()->save($post);
    }

    private function user(string $name): User {
        return User::query()->where('name', $name)->firstOrFail();
    }
}
