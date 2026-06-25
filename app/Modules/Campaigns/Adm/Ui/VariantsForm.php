<?php
namespace Coyote\Modules\Campaigns\Adm\Ui;

use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;
use Modules\Campaigns\VariantType;

class VariantsForm extends Form implements ValidatesWhenSubmitted {
    public function buildForm(): void {
        $this->add('type', 'select', [
            'label'   => 'Wymiary wariantu',
            'choices' => [
                'upload-standard'    => 'Banner (728 × 90)',
                'upload-sidebar'     => 'Rectangle (300 × 250)',
                'upload-leaderboard' => 'LeaderBoard (1140 × 90)',
            ],
            'rules'   => 'required|in:upload-standard,upload-sidebar,upload-leaderboard',
            'help'    => 'Baner poziomy zostanie wyświetlony na stronie głównej oraz na stronie z wątkami. '
                . 'Baner sidebar zostanie wyświetlony w sidebarze.',
        ]);
        $this->add('image_url', 'text', [
            'label' => 'Grafika wariantu',
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

    public function variantType(): VariantType {
        $type = (string)$this->getField('type');
        return match ($type) {
            'upload-standard'    => VariantType::Standard,
            'upload-sidebar'     => VariantType::Sidebar,
            'upload-leaderboard' => VariantType::LeaderBoard,
        };
    }
}
