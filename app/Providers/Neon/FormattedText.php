<?php
namespace Coyote\Providers\Neon;

use HTMLPurifier;
use HTMLPurifier_Config;

class FormattedText {
    private HTMLPurifier_Config $config;

    public function __construct() {
        $this->config = HTMLPurifier_Config::createDefault();
        $this->config->autoFinalize = false;
        $this->config->loadArray([
            'Core.Encoding'                  => 'UTF-8',
            'Core.ConvertDocumentToFragment' => false,

            'HTML.Allowed'   => 'i,b,u,p,ul,ol,li,br,strong,em',
            'HTML.TidyLevel' => 'none',

            'AutoFormat.AutoParagraph'          => true,
            'AutoFormat.RemoveEmpty'            => true,
            'AutoFormat.RemoveEmpty.RemoveNbsp' => true,

            'Output.CommentScriptContents' => false,
            'Output.FixInnerHTML'          => false,
            'Output.Newline'               => "\n",
        ]);
    }

    public function stripFormatting(string $formattedTextHtml): string {
        $text = $this->removeNbsp($formattedTextHtml);
        return $this->stripHtmlTags($text);
    }

    private function removeNbsp(string $formattedTextHtml): string|array {
        return \str_replace('&nbsp;', '', $formattedTextHtml);
    }

    private function stripHtmlTags(string $html): string {
        $instance = new HTMLPurifier();
        $firstIteration = $instance->purify($html, $this->config);
        $secondIteration = $instance->purify($firstIteration, $this->config);
        return $secondIteration;
    }
}
