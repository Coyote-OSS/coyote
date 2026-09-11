<?php
namespace Tests\Legacy\LaravelIntegration\Services\Form;

class SkillsForm extends \Coyote\Services\FormBuilder\Form
{
    public function buildForm()
    {
        $this
            ->add('name', 'text', [
                'label' => 'Nazwa',
                'rules' => 'required|string|max:100',
                'attr'  => [
                    'placeholder' => 'Np. java, c#',
                ],
            ])
            ->add('rate', 'text', [
                'label' => 'Ocena',
                'rules' => 'required|integer|min:1|max:6',
            ])
            ->add('order', 'hidden');
    }
}
