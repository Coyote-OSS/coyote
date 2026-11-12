<?php

namespace Coyote\Services\Parser\Extensions;

use League\CommonMark\Event\DocumentParsedEvent;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\ExternalLink\ExternalLinkProcessor;
use League\CommonMark\Node\Inline\AbstractInline;
use TRegx\CleanRegex\Exception\SubjectNotMatchedException;
use TRegx\CleanRegex\Pattern;

class YoutubeLinkProcessor
{
    use LinkSupport;

    private const YOUTUBE_HOSTS = ['youtube.com', 'youtu.be', 'www.youtube.com', 'www.youtu.be'];

    public function __invoke(DocumentParsedEvent $e): void
    {
        foreach ($e->getDocument()->iterator() as $link) {
            if (!($link instanceof Link)) {
                continue;
            }

            $components = parse_url($link->getUrl());

            if (!$this->isValidLink($components) || !$this->isYoutubeLink($link, $components) || $this->linkHasLabel($link)) {
                continue;
            }

            $path = trim($components['path'] ?? '', '/');

            $iframe = match (explode('/', $path)[0]) {
                'watch'  => $this->makeIframeFromFullPath($components),
                'shorts' => $this->makeIframeFromShortsPath($components),
                default  => $this->makeIframeFromShortPath($components),
            };

            if ($iframe) {
                $link->replaceWith($iframe);
            }
        }
    }

    private function isYoutubeLink(Link $link, array $components): bool
    {
        if ($link->parent() instanceof AbstractInline) {
            return false;
        }

        return ExternalLinkProcessor::hostMatches($components['host'], self::YOUTUBE_HOSTS);
    }

    private function makeIframeFromFullPath(array $components): ?Iframe
    {
        parse_str($components['query'] ?? '', $query);

        if (empty($query['v'])) {
            return null;
        }

        parse_str($components['fragment'] ?? '', $hash);

        return $this->makeIframe($query['v'], $this->timeToSeconds($hash['t'] ?? null));
    }

    /**
     * YouTube Shorts: youtube.com/shorts/{id}
     */
    private function makeIframeFromShortsPath(array $components): ?Iframe
    {
        $segments = explode('/', trim($components['path'], '/'));

        if (empty($segments[1])) {
            return null;
        }

        return $this->makeIframe($segments[1]);
    }

    private function makeIframeFromShortPath(array $components): ?Iframe
    {
        parse_str($components['query'] ?? '', $query);

        return $this->makeIframe(trim($components['path'], '/'), $this->timeToSeconds($query['t'] ?? null));
    }

    private function makeIframe(string $videoId, string $start = null): Iframe
    {
        $iframe = new Iframe();
        $iframe->data->set('src', 'https://youtube.com/embed/' . $videoId . ($start !== null ? "?start=$start" : ''));
        $iframe->data->set('class', 'youtube-player');
        $iframe->data->set('allowfullscreen', 'allowfullscreen');

        return $iframe;
    }

    private function timeToSeconds(?string $time): ?int
    {
        if (!$time) {
            return null;
        }

        $pattern = Pattern::of('(\d+)m(\d+)s');

        try {
            [$minutes, $seconds] = $pattern->match($time)->tuple(1, 2);
        } catch (SubjectNotMatchedException) {
            return (int) $time;
        }

        return $minutes * 60 + $seconds;
    }
}
