<?php

namespace Coyote\Http\Forms;

use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;

class BlockForm extends Form implements ValidatesWhenSubmitted {
    protected $theme = self::THEME_INLINE;

    public function buildForm() {
        $this
            ->add('name', 'text', [
                'rules' => 'required|string|max:40',
                'label' => 'Nazwa bloku',
            ])
            ->add('region', 'select', [
                'label'       => 'Region',
                'choices'     => $this->regions(),
                'empty_value' => '--',
            ])
            ->add('content', 'textarea', [
                'label' => 'Kod HTML',
                'rules' => 'required|string',
                'attr'  => [
                    'id' => 'code',
                ],
                'help'  => \htmlEntities(
                    'Dodanie tagów <script> może wymagać tymczasowego wyłączenia ' .
                    'zabezpieczeń Cloudflare (np. "detection tools", "managed ruleset", "waf", "xss", "app exploit").',
                ),
            ])
            ->add('is_enabled', 'checkbox', [
                'label' => 'Włączony',
            ])
            ->add('enable_sponsor', 'checkbox', [
                'label' => 'Włączony dla sponsorów',
            ])
            ->add('max_reputation', 'text', [
                'label' => 'Wyświetlaj tylko użytkownikom z reputacją od 0 do...',
                'help'  => 'Blok ten nie będzie wyświetlany użytkownikom, którzy posiadają więcej niż N punktów reputacji',
                'rules' => 'nullable|integer|min:1',
                'attr'  => [
                    'style' => 'width: 50px',
                ],
            ])
            ->add('submit', 'submit_with_delete', [
                'label'             => 'Zapisz',
                'attr'              => [
                    'data-submit-state' => 'Zapisywanie...',
                ],
                'delete_url'        => empty($this->data->id) ? '' : route('adm.blocks.delete', [$this->data->id]),
                'delete_visibility' => !empty($this->data->id),
            ]);
    }

    /**
     * @return array
     */
    private function regions() {
        return [
            'header'      => 'Nagłówek',
            'footer'      => 'Stopka',
            'head'        => 'Znacznik <head>',
            'body'        => 'Znacznik <body>',
            'wiki_footer' => 'Stopka artykułów',
            'bottom'      => 'Koniec strony',
        ];
    }
}
