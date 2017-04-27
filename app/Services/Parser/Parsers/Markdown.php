<?php

namespace Coyote\Services\Parser\Parsers;

use Coyote\Repositories\Contracts\UserRepositoryInterface as User;

// dziedziczymy po Parsedown a nie Parsedown Extra z uwagi na buga. Parsedown Extra wycina reszte linii jezeli
// w danej linii znajdzie sie tag. np. <ort>ktory</ort> w ogle => <ort>ktory</ort>
class Markdown extends \Parsedown implements ParserInterface
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var bool
     */
    private $enableHashParser = false;

    /**
     * @var bool
     */
    private $enableUserTagParser = true;

    /**
     * @var string
     */
    private $hashRoute = 'microblog.tag';

    /**
     * @param User $user
     * @throws \Exception
     */
    public function __construct(User $user)
    {
        $this->InlineTypes['@'][] = 'UserTag';
        $this->inlineMarkerList .= '@';

        $this->InlineTypes['#'][] = 'Hash';
        $this->inlineMarkerList .= '#';

        $this->user = $user;
    }

    /**
     * @param bool $flag
     * @return Markdown
     */
    public function setEnableHashParser(bool $flag)
    {
        $this->enableHashParser = $flag;

        return $this;
    }

    /**
     * @param boolean $flag
     * @return Markdown
     */
    public function setEnableUserTagParser(bool $flag)
    {
        $this->enableUserTagParser = $flag;

        return $this;
    }

    /**
     * @param string $text
     * @return string
     */
    public function parse($text)
    {
        // @see https://github.com/erusev/parsedown/issues/432
        // trzeba wylaczyc zamiane linkow na URL poniewaz nie dziala to prawidlowo (bug parsera)
        // robimy to osobno, w parserze Autolink
        $this->setUrlsLinked(false);

        return $this->text($text);
    }

    /**
     * @param array $excerpt
     * @return array|void
     */
    protected function inlineHash($excerpt)
    {
        if (!$this->enableHashParser) {
            return;
        }

        if (!$this->isSingleWord($excerpt)) {
            return;
        }

        if (preg_match('~#([\p{L}\p{Mn}0-9\._+-]+)~u', $excerpt['text'], $matches)) {
            $tag = mb_strtolower($matches[1]);

            return [
                'extent' => strlen($matches[0]),
                'element' => [
                    'name' => 'a',
                    'text' => $matches[0],
                    'attributes' => [
                        'href' => route($this->hashRoute, [$tag])
                    ]
                ]
            ];
        }
    }

    /**
     * We don't want <h1> in our text
     * We also need to disable this syntax due to parsedown error. #foo should NOT be parser
     * because markdown needs extra space after "#". Otherwise it is hashtag.
     *
     * DO NOT REMOVE THIS METHOD.
     *
     * @param $line
     * @return array|null
     */
    protected function blockHeader($line)
    {
        $block = parent::blockHeader($line);

        if (isset($block['element'])) {
            if ($block['element']['name'] == 'h1') {
                return null;
            }
        }

        return $block;
    }

    /**
     * Parse users login
     *
     * @param array $excerpt
     * @return array|null
     */
    protected function inlineUserTag($excerpt)
    {
        if (!$this->enableUserTagParser) {
            return null;
        }

        $text = &$excerpt['text'];
        $start = strpos($text, '@');

        if ($this->isSingleWord($excerpt)) {
            if (!isset($text[$start + 1])) {
                return null;
            }

            $exitChar = $text[$start + 1] === '{' ? '}' : ":,.\'\n "; // <-- space at the end
            $end = $this->strpos($text, $exitChar, $start);

            if ($end === false) {
                $end = mb_strlen($text) - $start;
            }

            $length = $end - $start;
            $start += 1;
            $end -= 1;

            if ($exitChar == '}') {
                $start += 1;
                $end -= 1;

                $length += 1;
            }

            $name = substr($text, $start, $end);

            // user name ends with ")" -- we strip if login is within bracket
            if (strlen($name) > 0 && $name[mb_strlen($name) - 1] === ')' && mb_strpos($name, '(') === false) {
                $name = mb_substr($name, 0, -1);
                $length -= 1;
            }

            $user = $this->user->findByName($name);

            $replacement = [
                'extent' => $length,
                'element' => [
                    'name' => 'a',
                    'text' => '@' . $name
                ]
            ];

            if ($user) {
                $replacement['element']['attributes'] = [
                    'href' => route('profile', [$user->id]),
                    'data-user-id' => $user->id,
                    'class' => 'mention'
                ];
            } else {
                $replacement['element']['name'] = 'strong';
            }

            return $replacement;
        }
    }

    /**
     * @param array $excerpt
     * @return bool
     */
    private function isSingleWord(&$excerpt)
    {
        // the whole text
        $context = &$excerpt['context'];
        // "@" or "#" position
        $start = mb_strpos($context, $excerpt['text']);
        // previous character (before "@" or "#")
        $preceding = mb_substr($context, $start - 1, 1);
        // next character (after "@" or "#")
        $following = mb_substr($context, $start + 1, 1);

        return mb_substr($excerpt['text'], $start + 1, 1) !== false
            && ($start === 0 || in_array($preceding, [' ', '(', "\n"]))
                && $following !== ' ';
    }

    /**
     * Find the position of the first occurrence of a character in a string
     *
     * @param $haystack
     * @param string $needle
     * @param int $offset
     * @return bool|mixed
     */
    private function strpos($haystack, $needle, $offset = 0)
    {
        $result = [];

        foreach (str_split($needle) as $char) {
            if (($pos = strpos($haystack, $char, $offset)) !== false) {
                $result[] = $pos;
            }
        }

        return $result ? min($result) : false;
    }
}
