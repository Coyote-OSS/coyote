<?php
namespace Tests\Legacy\LaravelIntegration\Services\Form;

class SampleForm extends \Coyote\Services\FormBuilder\Form
{
    public function buildForm()
    {
        $this
            ->add('name', 'text')
            ->add('email', 'text')
            ->add('group_id', 'select', [
                'choices' => [
                    1 => 'Admin',
                    2 => 'Moderator',
                ],
            ])
            ->add('bio', 'textarea')
            ->add('groups', 'choice', [
                'choices' => [
                    2 => 'Admin',
                    4 => 'Moderator',
                    8 => 'Unassigned Group',
                ],
            ]);
    }
}
