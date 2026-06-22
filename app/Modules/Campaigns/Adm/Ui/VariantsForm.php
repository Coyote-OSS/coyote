<?php
namespace Coyote\Modules\Campaigns\Adm\Ui;

use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;

class VariantsForm extends Form implements ValidatesWhenSubmitted {
    public function buildForm(): void {
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
        $this->add('image_url', 'text', [
            'label' => 'Grafika baneru',
            'rules' => 'required|url|max:255',
            'help'  => 'Podaj adres URL grafiki reklamowej. Skorzystaj z hostowania obrazów poniżej.',
        ]);
        $this->add('submit', 'submit', [
            'label' => 'Zapisz',
            'attr'  => ['data-submit-state' => 'Zapisywanie...'],
        ]);
    }

    public function messages(): array {
        return [
            'image_url.required' => 'Grafika baneru jest wymagana.',
        ];
    }
}
