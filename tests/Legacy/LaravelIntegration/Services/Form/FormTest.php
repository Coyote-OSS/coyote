<?php
namespace Tests\Legacy\LaravelIntegration\Services\Form;

use Faker\Factory;
use Illuminate\Routing\Redirector;
use Tests\Legacy\LaravelIntegration\TestCase;

class FormTest extends TestCase
{
    protected $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = app()->make('request');
        $this->request->setLaravelSession(app('session')->driver('array'));
    }

    public function testCreateFormAndSetAttributes()
    {
        $form = $this->createForm(TestForm::class, null, ['url' => 'http://foo.com']);
        $this->assertInstanceOf(\Illuminate\Http\Request::class, $form->getRequest());

        $this->assertEquals('http://foo.com', $form->getUrl());

        $form->setUrl('http://bar.com');
        $this->assertEquals('http://bar.com', $form->getUrl());

        $form->setAttr(['id' => 'submit-form']);
        $this->assertArrayHasKey('id', $form->getAttr());
        $this->assertEquals('http://bar.com', $form->getUrl());

        $form->setAttr(['id' => 'submit-form', 'url' => 'http://kung-fu.com']);
        $this->assertEquals('http://kung-fu.com', $form->getUrl());

        $this->assertEquals('POST', $form->getMethod());
        $form->setMethod('GET');
        $this->assertEquals('GET', $form->getMethod());

        $form->setAttr(['id' => 'foo-form']);
        $this->assertEquals('GET', $form->getMethod());
    }

    public function testBuildFormWithDefaultValues()
    {
        $form = $this->createForm(TestForm::class);
        $this->assertInstanceOf(\Illuminate\Http\Request::class, $form->getRequest());

        $tags = collect([
            ['id' => 1, 'name' => 'c++'],
            ['id' => 2, 'name' => 'c#'],
        ]);

        $form->add('name', 'text', ['value' => 'Admin']);
        $form->add('genre', 'select', [
            'choices'     => [
                'male' => 'Male', 'female' => 'Female',
            ],
            'empty_value' => '-- choose --',
            'value'       => 'female',
        ]);
        $form->add('confirm', 'checkbox', [
            'value' => 1,
        ]);
        $form->add('tags', 'collection', [
            'property'   => 'name',
            'value'      => $tags,
            'child_attr' => [
                'type' => 'text',
            ],
        ]);
        $form->add('experience', 'text', [
            'value' => 0,
        ]);

        $form->buildForm();

        $this->assertInstanceOf(\Coyote\Services\FormBuilder\Fields\Text::class, $form->get('name'));
        $this->assertInstanceOf(\Coyote\Services\FormBuilder\Fields\Text::class, $form->getField('name'));
        $this->assertInstanceOf(\Coyote\Services\FormBuilder\Fields\Text::class, $form->name);

        $this->assertInstanceOf(\Coyote\Services\FormBuilder\Fields\Select::class, $form->get('genre'));
        $this->assertInstanceOf(\Coyote\Services\FormBuilder\Fields\Checkbox::class, $form->get('confirm'));
        $this->assertInstanceOf(\Coyote\Services\FormBuilder\Fields\Collection::class, $form->get('tags'));

        $this->assertValue('name', 'Admin', $form);
        $this->assertValue('genre', 'female', $form);

        $form->genre->setValue(0);
        $this->assertValue('genre', 0, $form);
        $this->assertEquals('-- choose --', $form->genre->getEmptyValue());

        $this->assertValue('confirm', 1, $form);
        $this->assertTrue($form->confirm->isChecked());

        $form->get('confirm')->setValue(0);
        $this->assertValue('confirm', 0, $form);

        $form->get('confirm')->setChecked(true);
        $this->assertValue('confirm', 1, $form);

        $form->get('confirm')->setCheckedValue('human')->setValue('human');
        $this->assertTrue($form->get('confirm')->isChecked());

        $form->get('confirm')->setValue(false);
        $this->assertFalse($form->get('confirm')->isChecked());

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $form->get('tags')->getValue());
        $this->assertTrue(is_array($form->tags->getChildrenValues()));
        $this->assertTrue(is_array($form->tags->getChildren()));

        $this->assertEquals('c++', $form->tags->getChildren()[0]->getValue());
        $this->assertEquals('c#', $form->tags->getChildren()[1]->getValue());

        $tags = collect([
            ['id' => 1, 'name' => 'pascal'],
            ['id' => 2, 'name' => 'coyote'],
        ]);

        $form->get('tags')->setValue($tags);

        $this->assertEquals('pascal', $form->tags->getChildren()[0]->getValue());
        $this->assertEquals('coyote', $form->tags->getChildren()[1]->getValue());

        $this->assertTrue(0 === $form->get('experience')->getValue());
    }

    public function testBuildFormWithCollection()
    {
        $form = $this->createForm(TestForm::class);
        $tags = collect([
            ['id' => 1, 'name' => 'c++'],
            ['id' => 2, 'name' => 'c#'],
        ]);

        $form->add('tags_v1', 'collection', [
            'property'   => 'name',
            'value'      => $tags,
            'child_attr' => [
                'type' => 'text',
            ],
        ]);
        $form->add('tags_v2', 'collection', [
            'property'   => 'name',
            'child_attr' => [
                'type' => 'text',
            ],
            'value'      => $tags,
        ]);

        $form->buildForm();

        $this->assertEquals('c++', $form->tags_v1->getChildren()[0]->getValue());
        $this->assertEquals('c#', $form->tags_v1->getChildren()[1]->getValue());

        $this->assertEquals('c++', $form->tags_v2->getChildren()[0]->getValue());
        $this->assertEquals('c#', $form->tags_v2->getChildren()[1]->getValue());
    }

    public function testBuildFormWithEmptyCollection()
    {
        $form = $this->createForm(TestForm::class);

        $form->add('tags', 'collection', [
            'property'   => 'name',
            'child_attr' => [
                'type' => 'text',
            ],
        ]);

        $this->assertEmpty($form->get('tags')->getChildren());

        $tags = collect([
            ['id' => 1, 'name' => 'c++'],
            ['id' => 2, 'name' => 'c#'],
        ]);

        $form->get('tags')->setValue($tags);
        $this->assertNotEmpty($form->get('tags')->getChildren());

        $this->assertEquals('c++', $form->tags->getChildren()[0]->getValue());
        $this->assertEquals('c#', $form->tags->getChildren()[1]->getValue());
    }

    public function testBuildFormWithCollectionOfChildrenForms()
    {
        $form = $this->createForm(TestForm::class);
        $form
            ->add('name', 'text', [
                'value' => 'Adam Boduch',
            ])
            ->add('skills', 'collection', [
                'label'      => 'Umiejętności',
                'child_attr' => [
                    'type'  => 'child_form',
                    'class' => $this->createForm(SkillsForm::class),
                ],
            ]);

        $this->assertTrue($form->get('skills') instanceof \Coyote\Services\FormBuilder\Fields\Collection);
    }

    public function testBuildFormWithEmptyChildForm()
    {
        $form = $this->createForm(TestForm::class);
        $this->assertInstanceOf(\Illuminate\Http\Request::class, $form->getRequest());

        $child = $this->createForm(PollForm::class);
        $child->buildForm();

        $form->add('poll', 'child_form', ['class' => $child]);
        $form->buildForm();

        $this->assertNotEmpty($form->get('poll')->getForm()->getFields());
        $this->assertNotEmpty($form->get('poll')->getChildren());
        $this->assertEquals($form->get('poll')->getForm()->getFields(), $form->get('poll')->getChildren());

        $this->assertInstanceOf(\Illuminate\Http\Request::class, $form->get('poll')->getRequest());
        $this->assertEquals('poll[title]', $form->get('poll')->get('title')->getName());
        $this->assertEquals('poll[items]', $form->get('poll')->get('items')->getName());
    }

    public function testBuildFormWithChildFormAndFillUpWithArray()
    {
        $faker = Factory::create();
        $value = ['title' => $title = $faker->text(50), 'items' => $items = "Answer 1\nAnswer 2"];

        $this->fillUpChildFormWithData($value);
    }

    public function testBuildFormWithChildFormAndFillUpWithCollection()
    {
        $faker = Factory::create();
        $value = collect(['title' => $title = $faker->text(50), 'items' => $items = "Answer 1\nAnswer 2"]);

        $this->fillUpChildFormWithData($value);
    }

    private function fillUpChildFormWithData($value)
    {
        $form = $this->createForm(TestForm::class);

        $child = $this->createForm(PollForm::class);
        $child->buildForm();

        $form->add('poll', 'child_form', [
            'class' => $child,
            'value' => $value,
        ]);
        $form->buildForm();

        $this->assertEquals($value['title'], $form->get('poll')->getForm()->get('title')->getValue());
        $this->assertEquals($value['title'], $form->get('poll')->get('title')->getValue());
        $this->assertEquals($value['items'], $form->get('poll')->get('items')->getValue());
    }

    public function testBuildFormWithObjectValues()
    {
        $fake = Factory::create();

        $data = (object)[
            'name'     => $fake->name,
            'email'    => $fake->email,
            'bio'      => $fake->text,
            'group_id' => 2,
            'groups'   => [2, 8],
        ];

        // wypelnienie danymi
        $this->fillWithData($data);
    }

    public function testBuildFormWithArrayValues()
    {
        $fake = Factory::create();

        $data = [
            'name'     => $fake->name,
            'email'    => $fake->email,
            'bio'      => $fake->text,
            'group_id' => 2,
            'groups'   => [2, 8],
        ];

        $this->fillWithData($data);
    }

    public function testBuildFormAndFillUpWithCollection()
    {
        $faker = Factory::create();

        $data = collect([
            'name'     => $faker->name,
            'email'    => $faker->email,
            'bio'      => $faker->text(),
            'group_id' => 1,
            'groups'   => [2, 8],
        ]);

        $form = $this->createForm(SampleForm::class, $data);
        $form->buildForm();

        $this->assertValue('name', $data['name'], $form);
        $this->assertValue('email', $data['email'], $form);
        $this->assertValue('bio', $data['bio'], $form);
        $this->assertValue('group_id', $data['group_id'], $form);
        $this->assertValue('groups', $data['groups'], $form);
    }

    public function testBuildFormAndFillUpWithModel()
    {
        $model = new Model;

        $form = $this->createForm(SampleForm::class, $model);
        $form->buildForm();

        $form->remove('groups');
        $form->add('groups', 'choice', [
            'choices'  => [
                2 => 'Admin',
                4 => 'Moderator',
                8 => 'Unassigned Group',
            ],
            'property' => 'id',
        ]);

        $this->assertValue('name', $model->name, $form);
        $this->assertValue('email', $model->email, $form);
        $this->assertValue('bio', $model->bio, $form);
        $this->assertValue('group_id', $model->group_id, $form);

        $groups = $form->get('groups');

        $this->assertEquals(3, count($groups->getChildren()));
        $this->assertEquals('Admin', $groups->getChild(0)->getLabel());
        $this->assertEquals('Moderator', $groups->getChild(1)->getLabel());
        $this->assertEquals('Unassigned Group', $groups->getChild(2)->getLabel());

        $this->assertTrue($groups->getChild(0)->isChecked());
        $this->assertFalse($groups->getChild(1)->isChecked());
        $this->assertTrue($groups->getChild(2)->isChecked());

        $this->assertEquals(2, $groups->getChild(0)->getCheckedValue());
        $this->assertEquals(4, $groups->getChild(1)->getCheckedValue());
        $this->assertEquals(8, $groups->getChild(2)->getCheckedValue());

        $this->assertEquals([2, 8], $groups->getChildrenValues());
    }

    public function testPassesValidation()
    {
        $this->request['name'] = 'some name';
        $this->request['description'] = 'somedesc';

        $form = $this->createForm(TestForm::class);
        $form
            ->setMethod('GET')
            ->add('name', 'text', [
                'rules' => 'required|min:5',
            ])
            ->add('description', 'textarea', [
                'rules' => 'max:10',
            ]);

        $this->assertTrue($form->isValid());

        $this->assertEquals(
            ['name' => 'required|min:5', 'description' => 'max:10'],
            $form->rules(),
        );

        $this->assertEquals(
            ['name' => $this->request['name'], 'description' => $this->request['description']],
            $form->all(),
        );
    }

    public function testFailsValidation()
    {
        $this->request['name'] = '';
        $this->request['description'] = 'Lorem ipsum lores aaaaaaaaaaaaaaaaaaaaa';

        $form = $this->createForm(TestForm::class);
        $form
            ->setMethod('GET')
            ->add('name', 'text', [
                'rules' => 'required|min:5',
            ])
            ->add('description', 'textarea', [
                'rules' => 'max:10',
            ]);

        $validator = $form->validate();
        $this->assertFalse($validator->passes());

        $this->assertTrue($validator->errors()->has('name'));
        $this->assertTrue($validator->errors()->has('description'));
    }

    public function testRequiredElement()
    {
        $this->request['name'] = '';

        $form = $this->createForm(TestForm::class);
        $form
            ->setMethod('GET')
            ->add('name', 'text', [
                'required' => true,
                'rules'    => 'min:5',
            ]);

        $this->assertTrue($form->get('name')->isRequired());

        $validator = $form->validate();
        $this->assertFalse($validator->passes());

        $this->assertTrue($validator->errors()->has('name'));
        $this->assertEquals(
            ['name' => 'required|min:5'],
            $form->rules(),
        );
    }

    public function testMergeFieldOptions()
    {
        $form = $this->createForm(TestForm::class);
        $form
            ->add('description', 'textarea', [
                'attr'  => [
                    'tabindex' => 3,
                ],
                'rules' => 'max:10',
            ])
            ->add('title', 'text');

        $form->buildForm();

        $field = $form->getField('description');
        $field->mergeOptions(['attr' => ['tabindex' => 1, 'rows' => 10]]);

        $this->assertEquals(1, $field->getAttr()['tabindex']);
        $this->assertEquals(10, $field->getAttr()['rows']);
        $this->assertEquals('max:10', $field->getRules());

        $field = $form->getField('title');
        $field->mergeOptions(['attr' => ['tabindex' => 1, 'style' => 'color: red']]);

        $this->assertEquals(1, $field->getAttr()['tabindex']);
        $this->assertEquals('color: red', $field->getAttr()['style']);
    }

    private function fillWithData($data)
    {
        $form = $this->createForm(SampleForm::class);
        $form->buildForm();
        $form->setData($data);

        $this->assertInstanceOf(\Coyote\Services\FormBuilder\Fields\Text::class, $form->get('name'));
        $this->assertInstanceOf(\Coyote\Services\FormBuilder\Fields\Text::class, $form->get('email'));
        $this->assertInstanceOf(\Coyote\Services\FormBuilder\Fields\Textarea::class, $form->get('bio'));
        $this->assertInstanceOf(\Coyote\Services\FormBuilder\Fields\Select::class, $form->get('group_id'));
        $this->assertInstanceOf(\Coyote\Services\FormBuilder\Fields\Choice::class, $form->get('groups'));

        $data = (array)$data;

        $this->assertValue('name', $data['name'], $form);
        $this->assertValue('email', $data['email'], $form);
        $this->assertValue('bio', $data['bio'], $form);
        $this->assertValue('group_id', $data['group_id'], $form);
        $this->assertValue('groups', $data['groups'], $form);
    }

    private function assertValue($key, $value, $form)
    {
        $this->assertEquals($value, $form->get($key)->getValue());
        $this->assertEquals($value, $form->$key->getValue());
        $this->assertEquals($value, $form->getField($key)->getValue());
    }

    /**
     * @param $class
     * @param null $data
     * @param array $options
     * @return \Coyote\Services\FormBuilder\Form
     */
    private function createForm($class, $data = null, array $options = [])
    {
        $form = new $class();
        $form->setContainer(app())
            ->setRedirector(app(Redirector::class))
            ->setRequest($this->request)
            ->setData($data)
            ->setOptions($options);

        return $form;
    }
}
