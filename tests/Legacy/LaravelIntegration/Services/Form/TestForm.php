<?php
namespace Tests\Legacy\LaravelIntegration\Services\Form;

use Illuminate\Contracts\Validation\Validator;

class TestForm extends \Coyote\Services\FormBuilder\Form
{
    public function buildForm()
    {
    }

    protected function failedValidation(Validator $validator)
    {
        // don't throw validate exception
    }
}
