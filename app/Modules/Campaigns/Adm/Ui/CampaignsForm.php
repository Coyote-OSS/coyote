<?php
namespace Coyote\Modules\Campaigns\Adm\Ui;

use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;

class CampaignsForm extends Form implements ValidatesWhenSubmitted {
    public function buildForm(): void {
        $targetRequired = 'Jedno z pól "Aktywna do" lub "Docelowa liczba wyświetleń" jest konieczna do aktywowania kampanii.';
        $this
            ->add('name', 'text', [
                'label' => 'Nazwa kampanii',
                'help'  => 'Opcjonalna nazwa kampanii. Zastąpiła klucz kampanii i nie musi być unikatowa, pełni funkcję opisową.',
            ])
            ->add('is_premium', 'checkbox', [
                'label' => 'Kampania premium',
            ])
            ->add('redirect_url', 'text', [
                'label' => 'URL przekierowania',
                'rules' => 'required|url|max:255',
            ])
            ->add('active_since', 'datetime', [
                'label' => 'Aktywna od',
                'help'  => 'Nie wypełnienie tego pola skutkuje kampanią, która jest aktywna od razu.',
            ])
            ->add('active_until', 'datetime', [
                'label' => 'Aktywna do',
                'help'  => $targetRequired,
            ])
            ->add('target_views', 'number', [
                'label' => 'Docelowa liczba wyświetleń',
                'help'  => $targetRequired,
            ])
            ->add('description', 'textarea', [
                'label' => 'Opis kampanii',
                'help'  => 'Dodatkowe informacje na temat kampanii.',
            ]);
        $this->add('submit', 'submit_with_delete', [
            'label'             => 'Zapisz',
            'attr'              => ['data-submit-state' => 'Zapisywanie...'],
            'delete_visibility' => !empty($this->data->id),
            'delete_url'        => !empty($this->data->id)
                ? route('adm.campaigns.delete', [$this->data->id])
                : '',
        ]);
    }

    public function messages(): array {
        return [
            'campaign_key.required' => 'Klucz kampanii jest wymagany.',
            'sidebar.required'      => 'Grafika baneru bocznego jest wymagana.',
            'horizontal.required'   => 'Grafika baneru poziomego jest wymagana.',
            'redirect_url.required' => 'Adres przekierowania jest wymagany.',
            'campaign_key.unique'   => 'Już istnieje kampania z tym kluczem.',
        ];
    }
}
