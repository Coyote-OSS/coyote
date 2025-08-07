<?php
namespace Coyote\Services\Stream\Render;

use Coyote\Stream as Model;
use Jenssegers\Agent\Agent;

abstract class Render {
    /**
     * @var Model|array
     */
    protected $stream;

    /**
     * @param Model|array $stream
     */
    public function __construct($stream) {
        $this->stream = $stream;
    }

    /**
     * @return Model
     */
    public function render() {
        $agent = new Agent();
        $agent->setUserAgent($this->stream['browser']);

        $translator = trans();
        $id = 'stream.headline.' . array_get($this->stream, 'object.objectType');

        if ($translator->has($id . ':' . $this->stream['verb'])) {
            $id .= ':' . $this->stream['verb'];
        }

        $message = $translator->get($id);
        $parameters = $this->bindParameters($message);

        $this->stream['headline'] = $translator->get($id, $parameters);
        $this->stream['excerpt'] = $this->excerpt();
        $this->stream['excerptHref'] = $this->excerptHref();
        $this->stream['agent'] = $agent;

        return $this->stream;
    }

    /**
     * @param string $message
     * @return array
     */
    protected function bindParameters($message) {
        $parameters = [];
        $offset = 0;

        while (($start = strpos($message, ':', $offset)) > -1) {
            $offset = $start + 1;
            $end = strpos($message, ' ', $offset);

            if ($end === false) {
                $end = strlen($message);
            }

            $parameter = substr($message, $start + 1, $end - 1 - $start);
            if (method_exists($this, $parameter)) {
                $parameters[$parameter] = $this->$parameter();
            }
        }

        return $parameters;
    }

    /**
     * @return string
     */
    protected function actor() {
        return link_to(
            array_get($this->stream, 'actor.url'),
            array_get($this->stream, 'actor.displayName'),
            ['data-user-id' => array_get($this->stream, 'actor.id')],
        );
    }

    /**
     * @return mixed
     */
    protected function excerpt() {
        return array_get($this->stream, 'object.displayName');
    }

    protected function excerptHref(): ?string {
        return array_get($this->stream, 'object.url');
    }

    /**
     * @return mixed
     */
    protected function verb() {
        return trans('stream.verbs.' . $this->stream['verb']);
    }

    /**
     * @return string
     */
    protected function object() {
        return link_to(
            array_get($this->stream, 'object.url'),
            (string)trans('stream.nouns.' . array_get($this->stream, 'object.objectType')),
        );
    }

    /**
     * @return string
     */
    protected function target() {
        return link_to(
            array_get($this->stream, 'target.url'),
            str_limit(array_get($this->stream, 'target.displayName'), 64),
            ['title' => array_get($this->stream, 'target.displayName')],
        );
    }

    /**
     * @return string
     */
    protected function objectName() {
        return link_to(
            array_get($this->stream, 'object.url'),
            array_get($this->stream, 'object.displayName'),
            ['title' => array_get($this->stream, 'object.displayName')],
        );
    }
}
