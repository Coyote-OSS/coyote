<?php
namespace Coyote\Modules\Campaigns\Adm\Ui;

use Coyote\Modules\Campaigns\Eloquent;
use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;

class VariantsForm extends Form implements ValidatesWhenSubmitted {
    public function buildForm(): void {
        /** @var Eloquent\Campaign $campaign */
        $campaign = $this->data;
        $this->add('campaign_key', 'text', [
            'label' => 'Dla kampaniii',
            'attr'  => ['disabled' => 'disabled'],
            'value' => $campaign?->campaign_key,
            'help'  => 'Klucz kampanii do której dodawany jest wariant.',
        ]);
        $this->add('image_url', 'text', [
            'label' => 'Grafika baneru',
            'rules' => 'required|url|max:255',
            'help'  => 'Podaj adres URL grafiki reklamowej. Skorzystaj z hostowania obrazów poniżej.',
        ]);
        $this->add('type', 'select', [
            'label'   => 'Rodzaj baneru',
            'choices' => [
                'horizontal' => 'Poziomy',
                'sidebar'    => 'Panel boczny',
            ],
            'rules'   => 'required|in:horizontal,sidebar',
            'help'    => 'Baner poziomy zostanie wyświetlony na stronie głównej oraz na stronie z wątkami. '
                . 'Baner sidebar zostanie wyświetlony w sidebarze.',
        ]);
        $this->add('submit', 'submit', [
            'label' => 'Zapisz',
            'attr'  => ['data-submit-state' => 'Zapisywanie...'],
        ]);
    }

    public function messages(): array {
        return [
//            'campaign_key.required' => 'Klucz kampanii jest wymagany.',
//            'sidebar.required'      => 'Grafika baneru bocznego jest wymagana.',
//            'horizontal.required'   => 'Grafika baneru poziomego jest wymagana.',
//            'redirect_url.required' => 'Adres przekierowania jest wymagany.',
//            'campaign_key.unique'   => 'Już istnieje kampania z tym kluczem.',
        ];
    }
}
