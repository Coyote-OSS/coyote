<?php
namespace Coyote\Http\Controllers\Adm;

use Coyote\Domain\StringHtml;
use Coyote\User;
use Illuminate\Foundation\Application;
use Illuminate\Redis\RedisManager;
use Illuminate\View\View;

class DashboardController extends BaseController {
    public function index(): View {
        return $this->view('adm.dashboard', [
            'checklist'       => [
                $this->directoryWritable('storage/', \storage_path()),
                $this->directoryWritable('uploads/', \public_path()),
                [
                    'label' => 'Redis włączony',
                    'value' => \config('cache.default'),
                ],
                [
                    'label' => new StringHtml('Ilość połączeń Redis - <code>' . $this->redisConnections() . '</code>'),
                    'value' => true,
                ],
                [
                    'label' => new StringHtml('PHP - <code>' . \PHP_VERSION . '</code>'),
                    'value' => true,
                ],
                [
                    'label' => new StringHtml('Laravel - <code>' . Application::VERSION . '</code>'),
                    'value' => true,
                ],
            ],
            'cohortCanAccess' => $this->user()->can('adm-payment'),
            'cohortByStream'  => [
                'downloadUrl'  => route('adm.cohort.download', ['by' => 'stream']),
                'downloadDate' => date('Y-m-d'),
            ],
            'cohortByView'    => [
                'downloadUrl'  => route('adm.cohort.download', ['by' => 'view']),
                'downloadDate' => date('Y-m-d'),
            ],
        ]);
    }

    public function directoryWritable(string $basePath, string $path): array {
        $permission = \decOct(\filePerms($path) & 0777);
        return [
            'label' => new StringHtml("Katalog <code>$basePath</code> ma prawa do zapisu - <code>$permission</code>"),
            'value' => \is_writeable(\storage_path()),
        ];
    }

    private function redisConnections(): ?int {
        /** @var RedisManager $redis */
        $redis = app('redis');
        $clientsInfo = $redis->command('info', ['clients']);
        return $clientsInfo['connected_clients'] ?? null;
    }

    private function user(): User {
        /** @var User $user */
        $user = auth()->user();
        return $user;
    }
}
