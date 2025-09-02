<?php

namespace Coyote\Http\Forms\User;

use Coyote\Repositories\Contracts\GroupRepositoryInterface;
use Coyote\Services\Geocoder\GeocoderInterface;

class AdminForm extends SettingsForm {
    public function __construct(GeocoderInterface $geocoder, private GroupRepositoryInterface $group) {
        parent::__construct($geocoder);
    }

    public function buildForm(): void {
        $this->add('name', 'text', [
            'label' => 'Nazwa użytkownika',
            'rules' => 'required|min:2|max:28|username|user_unique:' . $this->getData()->id,
        ]);

        parent::buildForm();

        $this
            ->add('delete_photo', 'checkbox', [
                'label' => 'Usuń zdjęcie',
            ])
            ->add('groups', 'choice', [
                'label'    => 'Grupy użytkownika',
                'choices'  => $this->group->pluck('name', 'id'),
                'property' => 'id',
            ])
            ->add('is_confirm', 'checkbox', [
                'label' => 'Potwierdzony adres e-mail',
                'rules' => 'bool',
            ])
            ->add('is_verified', 'checkbox', [
                'label' => 'Potwierdzony numer telefonu',
                'rules' => 'bool',
            ])
            ->add('is_active', 'checkbox', [
                'label' => 'Konto aktywne',
                'rules' => 'bool',
            ])
            ->add('marketing_agreement', 'checkbox', [
                'label' => 'Zgoda marketingowa',
                'rules' => 'bool',
            ])
            ->add('is_sponsor', 'checkbox', [
                'label' => 'Sponsor',
                'rules' => 'bool',
            ]);
    }
}
