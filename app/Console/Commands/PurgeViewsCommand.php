<?php
namespace Coyote\Console\Commands;

use Carbon\Carbon;
use Coyote\Repositories\Contracts\PageRepositoryInterface as PageRepository;
use Illuminate\Console\Command;
use Illuminate\Database\Connection as Db;
use Illuminate\Support;

class PurgeViewsCommand extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coyote:counter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Increment pages views';

    /**
     * @var Db
     */
    private $db;

    /**
     * @var PageRepository
     */
    private $page;

    /**
     * @var mixed
     */
    private $redis;

    /**
     * @param Db $db
     * @param PageRepository $page
     */
    public function __construct(Db $db, PageRepository $page) {
        parent::__construct();

        $this->db = $db;
        $this->page = $page;
        $this->redis = app('redis');
    }

    /**
     * @throws \Exception
     */
    public function handle() {
        // get hits as serialized arrays
        $keys = $this->redis->smembers('hits');

        if (!$keys) {
            return;
        }

        // hits as groupped collection
        $pages = collect(array_map('unserialize', $keys))->groupBy('path');

        foreach ($pages as $path => $hits) {
            $this->saveUrlVisit($hits);

            /** @var \Coyote\Page $page */
            $page = $this->page->findByPath('/' . $path);

            $this->commit($page, $hits);
        }

        return 0;
    }

    /**
     * @param \Coyote\Page $page
     * @param \Illuminate\Support\Collection $hits
     * @throws \Exception
     */
    private function commit($page, $hits) {
        $keys = array_map('serialize', $hits->toArray());

        foreach ($keys as $key) {
            $this->redis->srem('hits', $key);
        }

        if (empty($page->id)) {
            return; // hits to non-existing page will be lost
        }

        $content = $page->content()->getResults();

        if (!$content) {
            return;
        }

        try {
            $this->db->beginTransaction();
            $content->timestamps = false;
            $content->increment('views', count($hits));
            $this->db->commit();
            $this->info('Added ' . count($hits) . ' views to: ' . $page->path);
        } catch (\Exception $e) {
            $this->db->rollBack();

            logger()->error($e->getMessage());
        }
    }

    private function saveUrlVisit(Support\Collection $hits): void {
        $batchInsert = $hits
            ->map(fn(array $hit): array => [
                'url'        => $hit['path'],
                'created_at' => new Carbon($hit['timestamp']),
            ])
            ->toArray();
        $this->db->table('url_visits')->insert($batchInsert);
    }
}
