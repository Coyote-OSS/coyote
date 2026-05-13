<?php
namespace Coyote\Modules\Campaigns\Adm;

use Coyote\Services\FormBuilder\Form;
use Coyote\Services\FormBuilder\ValidatesWhenSubmitted;

class CampaignsForm extends Form implements ValidatesWhenSubmitted {
    protected $theme = self::THEME_INLINE;

    public function buildForm(): void {
        $this
            ->add('campaign_key', 'text', [
                'label' => 'Klucz kampanii',
                'rules' => 'required|string|max:100',
                'help'  => 'Klucz kampanii musi być unikalny (e.g. <code>mobileViking</code>, <code>myDevil</code>)',
            ])
            ->add('sidebar', 'text', [
                'label' => 'Baner boczny - Rectangle/RectangleXl (narrow-250/narrow-600)',
                'rules' => 'required|string|max:255',
                'help'  => 'Podaj adres URL grafiki reklamowej.',
            ])
            ->add('horizontal', 'text', [
                'label' => 'Baner poziomy - Banner/BannerXL (wide-90/wide-250)',
                'rules' => 'required|string|max:255',
                'help'  => 'Podaj adres URL grafiki reklamowego.',
            ])
            ->add('redirect_url', 'text', [
                'label' => 'URL przekierowania',
                'rules' => 'required|url|max:255',
            ])
            ->add('submit', 'submit_with_delete', [
                'label'             => 'Zapisz',
                'attr'              => ['data-submit-state' => 'Zapisywanie...'],
                'delete_visibility' => !empty($this->data->id),
                'delete_url'        => !empty($this->data->id)
                    ? route('adm.campaigns.delete', [$this->data->id])
                    : '',
            ]);
    }
}
