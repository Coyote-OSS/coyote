<?php
namespace Coyote\Modules\Campaigns\Adm\Ui;

use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;
use Illuminate\Validation\Rule;

class CampaignsForm extends Form implements ValidatesWhenSubmitted {
    public function buildForm(): void {
        $this
            ->add('campaign_key', 'text', [
                'label' => 'Klucz kampanii',
                'rules' => [
                    'string', 'min:2', 'max:32',
                    'required',
                    Rule::unique('module_campaigns')->ignore($this->data?->id),
                ],
                'help'  => 'Klucz kampanii musi być unikalny (e.g. <code>mobileViking</code>, <code>myDevil</code>).',
                'attr'  => $this->campaignKeyAttributes(),
            ])
            ->add('redirect_url', 'text', [
                'label' => 'URL przekierowania',
                'rules' => 'required|url|max:255',
            ])
            ->add('sidebar', 'text', [
                'label' => 'Baner boczny (narrow-250/narrow-600)',
                'rules' => 'required|string|max:255',
                'help'  => 'Podaj adres URL grafiki reklamowej. Baner boczny zostanie wyświetlony przy reputacji użytkowników oraz w panelu bocznym na forum.',
            ])
            ->add('horizontal', 'text', [
                'label' => 'Baner poziomy (wide-90/wide-250)',
                'rules' => 'required|string|max:255',
                'help'  => 'Podaj adres URL grafiki reklamowego.',
            ]);
        $this->add('active_since', 'datetime', [
            'label' => 'Aktywna od',
            'help'  => 'Nie wypełnienie tego pola skutkuje kampanią, która nie jest aktywna.',
        ]);
        $this->add('active_until', 'datetime', [
            'label' => 'Aktywna do',
            'help'  => 'Nie wypełnienie tego pola skutkuje kampanią, która nie jest aktywna.',
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

    private function campaignKeyAttributes(): array {
        return $this->canEditCampaignKey() ? [] : ['readonly' => 'readonly'];
    }

    private function canEditCampaignKey(): bool {
        if ($this->data === null) {
            return true;
        }
        return $this->data->campaign_key === null;
    }
}
