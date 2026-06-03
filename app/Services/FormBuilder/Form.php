<?php

namespace Coyote\Services\FormBuilder;

abstract class Form extends FormRequest implements FormInterface {
    use CreateFieldTrait, RenderTrait;

    const GET = 'GET';
    const POST = 'POST';

    const THEME_INLINE = 'forms.themes.inline';
    const THEME_HORIZONTAL = 'forms.themes.horizontal';

    /**
     * It's public so we can use use attr from twig
     *
     * @var array
     */
    public $attr = [
        'method' => self::POST,
    ];

    /**
     * @var string
     */
    protected $theme = self::THEME_HORIZONTAL;

    /**
     * @var string
     */
    protected $template = 'form';

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var mixed|array
     */
    protected $data;

    /**
     * Form constructor.
     */
    public function __construct() {
        $this->events = new FormEvents($this);
    }

    /**
     * @return mixed
     */
    abstract public function buildForm();

    /**
     * @inheritdoc
     */
    public function rebuildForm() {
        $this->fields = [];
        $this->buildForm();
    }

    /**
     * @param string $name
     * @param \Closure $listener
     * @return $this
     */
    public function addEventListener($name, \Closure $listener) {
        $this->events->addListener($name, $listener);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function add($name, $type, array $options = []) {
        $this->fields[$name] = $this->makeField($name, $type, $this, $options);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function remove($name) {
        unset($this->fields[$name]);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addAfter($after, $name, $type, array $options = []) {
        $offset = array_search($after, array_keys($this->fields));

        $beforeFields = array_slice($this->fields, 0, $offset + 1);
        $afterFields = array_slice($this->fields, $offset + 1);

        $this->fields = $beforeFields;

        $this->add($name, $type, $options);

        $this->fields += $afterFields;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMethod() {
        return $this->attr['method'];
    }

    /**
     * @inheritdoc
     */
    public function setMethod($method) {
        $this->attr['method'] = $method;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUrl() {
        return $this->attr['url'];
    }

    /**
     * @inheritdoc
     */
    public function setUrl($url) {
        $this->attr['url'] = $url;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAttr() {
        return $this->attr;
    }

    /**
     * @inheritdoc
     */
    public function setAttr($attr) {
        // user want's to set attributes BUT maybe he does not want to override url attribute?
        $only = array_only($this->attr, ['url', 'method']);

        $this->attr = array_merge($only, $attr);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getData() {
        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function setData($data, $rebuildForm = true) {
        $this->data = $data;

        if ($rebuildForm && !empty($this->fields)) {
            $this->rebuildForm();
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setOptions(array $options = []) {
        foreach ($options as $key => $values) {
            $methodName = 'set' . ucfirst(camel_case($key));

            if (method_exists($this, $methodName)) {
                $this->$methodName($values);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function errors() {
        return $this->request->session()->get('errors');
    }

    /**
     * @inheritdoc
     */
    public function getField($field) {
        return $this->fields[$field] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function get($field) {
        return $this->getField($field);
    }

    public function getValue($field) {
        return $this->getField($field)->getValue();
    }

    /**
     * @inheritdoc
     */
    public function getFields() {
        return $this->fields;
    }

    /**
     * @inheritdoc
     */
    public function render() {
        $this->events->dispatch(FormEvents::PRE_RENDER);

        return $this->view($this->getViewPath($this->getTemplate()), [
            'form'   => $this,
            'fields' => $this->fields,
        ])->render();
    }

    /**
     * @inheritdoc
     */
    public function renderForm() {
        $this->events->dispatch(FormEvents::PRE_RENDER);

        return $this->view($this->getWidgetPath(), ['form' => $this])->render();
    }

    /**
     * @inheritdoc
     */
    public function all() {
        $values = [];

        foreach ($this->fields as $field) {
            $values[$field->getName()] = $field->getValue();
        }

        return $values;
    }

    /**
     * @inheritdoc
     */
    public function toJson() {
        return json_encode($this->all());
    }

    /**
     * @inheritdoc
     */
    public function isSubmitted() {
        return $this->request->method() === $this->getMethod();
    }

    /**
     * @return string
     */
    protected function getWidgetName() {
        return 'form_widget';
    }

    /**
     * Set up the validation rules
     */
    protected function setupRules() {
        $this->rules = (new Rules($this))->getRules();
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name) {
        return isset($this->fields[$name]);
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name) {
        return $this->getField($name);
    }
}
