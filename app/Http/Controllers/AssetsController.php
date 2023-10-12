<?php
namespace Coyote\Http\Controllers;

use Coyote\Http\Requests\AssetRequest;
use Coyote\Models\Asset;
use Coyote\Post;
use Coyote\Services\Assets\Thumbnail;
use Coyote\Services\Assets\Url;
use Coyote\Services\Media\Filters\Opg;
use Fusonic\OpenGraph\Consumer;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Http\Factory\Guzzle\RequestFactory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Connection;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class AssetsController extends Controller
{
    public function opengraph(Request $request, Connection $db, Thumbnail $thumbnail)
    {
        $this->validate($request, [
            'url' => 'required|url'
        ]);

        $client = new Client(['headers' => ['User-Agent' => 'facebookexternalhit/1.1']]);
        $consumer = new Consumer($client, new RequestFactory());

        $url = $request->get('url');
        try {
            $object = $consumer->loadUrl($url);

            if (!count($object->images)) {
                return response("No images to save.", 404);
            }

            $extension = pathinfo(parse_url($object->images[0]->url, PHP_URL_PATH), PATHINFO_EXTENSION);

            $filename = $this->humanName($extension);
            $tmpPath = sys_get_temp_dir() . '/' . $filename;

            $db->beginTransaction();

            file_put_contents($tmpPath, file_get_contents($object->images[0]->url));

            $uploadedFile = UploadedFile::createFromBase(new UploadedFile($tmpPath, $filename));
            $path = $uploadedFile->store($this->userId);

            $thumbnail->open($path)->setFilter(new Opg())->store($path);

            $asset = Asset::create([
                'name'     => $uploadedFile->getClientOriginalName(),
                'path'     => $path,
                'size'     => $uploadedFile->getSize(),
                'mime'     => $uploadedFile->getMimeType(),
                'metadata' => [
                    'title'       => $object->title,
                    'description' => $object->description,
                    'url'         => $this->hasDomainOrReturn($object->url, $url)
                ]
            ]);

            $db->commit();
        } catch (\ErrorException|ConnectException|RequestException $exception) {
            $db->rollBack();
            logger()->error($exception);

            abort(404);
        } catch (\Exception $exception) {
            $db->rollBack();

            throw $exception;
        } finally {
            @unlink($tmpPath);
        }

        return array_merge($asset->toArray(), ['url' => (string)Url::make($asset)]);
    }

    public function upload(AssetRequest $request)
    {
        $uploadedFile = $request->file('asset');
        $path = $uploadedFile->store($this->userId);

        $asset = Asset::create([
            'name' => $uploadedFile->getClientOriginalName() !== 'blob' ? $uploadedFile->getClientOriginalName() : $this->humanName($path),
            'path' => $path,
            'size' => $uploadedFile->getSize(),
            'mime' => $uploadedFile->getMimeType()
        ]);

        return array_merge($asset->toArray(), ['url' => (string)Url::make($asset)]);
    }

    public function download(Filesystem $filesystem, Asset $asset, string $name = null)
    {
        abort_if(!$asset->content, 404);

        if ($asset->content_type === Post::class) {
            $this->authorize('access', $asset->content->forum);
        }

        set_time_limit(0);

        $asset->count = $asset->count + 1;
        $asset->save();

        $headers = [
            'Content-Type' => 'Content-Type: ' . $asset->mime
        ];

        if (!$asset->isImage()) {
            $headers['Content-Disposition'] = 'attachment; filename="' . $asset->name . '"';
        }

        return response()->make($filesystem->get($asset->path), 200, $headers);
    }

    private function humanName(string $path): string
    {
        $extension = \pathInfo($path, \PATHINFO_EXTENSION);
        return 'screenshot-' . date('YmdHis') . '.' . \strToLower($extension);
    }

    private function hasDomainOrReturn(string $subjectUrl, string $originalUrl): string
    {
        if (\array_key_exists('host', \parse_url($subjectUrl))) {
            return $subjectUrl;
        }
        return $originalUrl;
    }
}
