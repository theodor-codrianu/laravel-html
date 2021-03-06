<?php

namespace Spatie\Html;

use Spatie\Html\Elements\A;
use Illuminate\Http\Request;
use Spatie\Html\Elements\Div;
use Spatie\Html\Elements\Form;
use Spatie\Html\Elements\Span;
use Spatie\Html\Elements\Input;
use Spatie\Html\Elements\Label;
use Spatie\Html\Elements\Button;
use Spatie\Html\Elements\Legend;
use Spatie\Html\Elements\Option;
use Spatie\Html\Elements\Select;
use Spatie\Html\Elements\Element;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Spatie\Html\Elements\Fieldset;
use Spatie\Html\Elements\Textarea;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Support\Htmlable;

class Html
{
    use Macroable;

    /** @var \Illuminate\Http\Request */
    protected $request;

    /** @var \ArrayAccess|array */
    protected $model;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param string|null $href
     * @param string|null $text
     *
     * @return \Spatie\Html\Elements\A
     */
    public function a($href = null, $contents = null)
    {
        return A::create()
            ->attributeIf($href, 'href', $href)
            ->html($contents);
    }

    /**
     * @param string|null $type
     * @param string|null $text
     *
     * @return \Spatie\Html\Elements\Button
     */
    public function button($contents = null, $type = null)
    {
        return Button::create()
            ->attributeIf($type, 'type', $type)
            ->html($contents);
    }

    /**
     * @param \Illuminate\Support\Collection|iterable|string $classes
     *
     * @return \Illuminate\Contracts\Support\Htmlable
     */
    public function class($classes): Htmlable
    {
        if ($classes instanceof Collection) {
            $classes = $classes->toArray();
        }

        $attributes = new Attributes();
        $attributes->addClass($classes);

        return new HtmlString(
            $attributes->render()
        );
    }

    /**
     * @param string|null $name
     * @param bool $checked
     * @param string|null $value
     *
     * @return \Spatie\Html\Elements\Input
     */
    public function checkbox($name = null, $checked = false, $value = '1')
    {
        return $this->input('checkbox', $name, $value)
            ->attributeIf((bool) $this->old($name, $checked), 'checked');
    }

    /**
     * @param \Spatie\Html\HtmlElement|string|null $contents
     *
     * @return \Spatie\Html\Elements\Div
     */
    public function div($contents = null)
    {
        return Div::create()->children($contents);
    }

    /**
     * @param string|null $name
     * @param string|null $value
     *
     * @return \Spatie\Html\Elements\Input
     */
    public function email($name = '', $value = '')
    {
        return $this->input('email', $name, $value);
    }

    /**
     * @param string $tag
     *
     * @return \Spatie\Html\Elements\Element
     */
    public function element($tag)
    {
        return Element::withTag($tag);
    }

    /**
     * @param string|null $type
     * @param string|null $name
     * @param string|null $value
     *
     * @return \Spatie\Html\Elements\Input
     */
    public function input($type = null, $name = null, $value = null)
    {
        return Input::create()
            ->attributeIf($type, 'type', $type)
            ->attributeIf($name, 'name', $this->fieldName($name))
            ->attributeIf($name, 'id', $this->fieldName($name))
            ->attributeIf($name && $this->old($name, $value), 'value', $this->old($name, $value));
    }

    /**
     * @param \Spatie\Html\HtmlElement|string|null $legend
     *
     * @return \Spatie\Html\Elements\Fieldset
     */
    public function fieldset($legend = null)
    {
        return $legend ?
            Fieldset::create()->legend($legend) :
            Fieldset::create();
    }

    /**
     * @param string $method
     * @param string|null $action
     *
     * @return \Spatie\Html\Elements\Form
     */
    public function form($method = 'POST', $action = null)
    {
        $method = strtoupper($method);
        $form = Form::create();

        // If Laravel needs to spoof the form's method, we'll append a hidden
        // field containing the actual method
        if (in_array($method, ['DELETE', 'PATCH', 'PUT'])) {
            $form = $form->addChild($this->hidden('_method')->value($method));
        }

        // On any other method that get, the form needs a CSRF token
        if ($method !== 'GET') {
            $form = $form->addChild($this->token());
        }

        return $form
            ->method($method === 'GET' ? 'GET' : 'POST')
            ->attributeIf($action, 'action', $action);
    }

    /**
     * @param string|null $name
     * @param string|null $value
     *
     * @return \Spatie\Html\Elements\Input
     */
    public function hidden($name = null, $value = null)
    {
        return $this->input('hidden', $name, $value);
    }

    /**
     * @param \Spatie\Html\HtmlElement|iterable|string|null $contents
     * @param string|null $for
     *
     * @return \Spatie\Html\Elements\Label
     */
    public function label($contents = null, $for = null)
    {
        return Label::create()
            ->attributeIf($for, 'for', $this->fieldName($for))
            ->children($contents);
    }

    /**
     * @param \Spatie\Html\HtmlElement|string|null $contents
     *
     * @return \Spatie\Html\Elements\Legend
     */
    public function legend($contents = null)
    {
        return Legend::create()->html($contents);
    }

    /**
     * @param string $email
     * @param string|null $text
     *
     * @return \Spatie\Html\Elements\A
     */
    public function mailto($email, $text = null)
    {
        return $this->a('mailto:'.$email, $text);
    }

    /**
     * @param string|null $name
     * @param iterable $options
     * @param string|iterable|null $value
     *
     * @return \Spatie\Html\Elements\Select
     */
    public function multiselect($name = null, $options = [], $value = null)
    {
        return Select::create()
            ->attributeIf($name, 'name', $this->fieldName($name))
            ->attributeIf($name, 'id', $this->fieldName($name))
            ->options($options)
            ->value($name ? $this->old($name, $value) : $value)
            ->multiple();
    }

    /**
     * @param string|null $text
     * @param string|null $value
     * @param bool $selected
     *
     * @return \Spatie\Html\Elements\Option
     */
    public function option($text = null, $value = null, $selected = false)
    {
        return Option::create()
            ->text($text)
            ->value($value)
            ->selectedIf($selected);
    }

    /**
     * @param string|null $value
     *
     * @return \Spatie\Html\Elements\Input
     */
    public function password($name = null)
    {
        return $this->input('password', $name);
    }

    /**
     * @param string|null $name
     * @param bool $checked
     * @param string|null $value
     *
     * @return \Spatie\Html\Elements\Input
     */
    public function radio($name = null, $checked = false, $value = null)
    {
        return $this->input('radio', $name, $value)
            ->attributeIf((bool) $this->old($name, $checked), 'checked');
    }

    /**
     * @param string|null $name
     * @param iterable $options
     * @param string|iterable|null $value
     *
     * @return \Spatie\Html\Elements\Select
     */
    public function select($name = null, $options = [], $value = null)
    {
        return Select::create()
            ->attributeIf($name, 'name', $this->fieldName($name))
            ->attributeIf($name, 'id', $this->fieldName($name))
            ->options($options)
            ->value($name ? $this->old($name, $value) : $value);
    }

    /**
     * @param \Spatie\Html\HtmlElement|string|null $contents
     *
     * @return \Spatie\Html\Elements\Span
     */
    public function span($contents = null)
    {
        return Span::create()->children($contents);
    }

    /**
     * @param string|null $test
     *
     * @return \Spatie\Html\Elements\Button
     */
    public function submit($text = null)
    {
        return $this->button($text, 'submit');
    }

    /**
     * @param string $number
     * @param string|null $text
     *
     * @return \Spatie\Html\Elements\A
     */
    public function tel($number, $text = null)
    {
        return $this->a('tel:'.$number, $text);
    }

    /**
     * @param string|null $name
     * @param string|null $value
     *
     * @return \Spatie\Html\Elements\Input
     */
    public function text($name = null, $value = null)
    {
        return $this->input('text', $name, $value);
    }

    /**
     * @param string|null $name
     * @param string|null $value
     *
     * @return \Spatie\Html\Elements\Textarea
     */
    public function textarea($name = null, $value = null)
    {
        return Textarea::create()
            ->attributeIf($name, 'name', $this->fieldName($name))
            ->attributeIf($name, 'id', $this->fieldName($name))
            ->value($this->old($name, $value));
    }

    /**
     * @return \Spatie\Html\Elements\Input
     */
    public function token()
    {
        return $this->hidden('_token')->value($this->request->session()->token());
    }

    /**
     * @param \ArrayAccess|array $model
     *
     * @return $this
     */
    public function model($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @param \ArrayAccess|array $model
     * @param string|null $method
     * @param string|null $action
     *
     * @return \Spatie\Html\Elements\Form
     */
    public function modelForm($model, $method = 'POST', $action = null): Form
    {
        $this->model($model);

        return $this->form($method, $action);
    }

    /**
     * @return $this
     */
    public function endModel()
    {
        $this->model = null;

        return $this;
    }

    /**
     * @return \Illuminate\Contracts\Support\Htmlable
     */
    public function closeModelForm(): Htmlable
    {
        $this->endModel();

        return $this->form()->close();
    }

    /**
     * @param string $name
     * @param mixed $value
     *
     * @return mixed
     */
    protected function old($name, $value = null)
    {
        if (empty($name)) {
            return;
        }

        // If there's no default value provided, and the html builder currently
        // has a model assigned, try to retrieve a value from the model.
        if (empty($value) && $this->model) {
            $value = $this->model[$name] ?? '';
        }

        return $this->request->old($name, $value);
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function fieldName($name)
    {
        return $name;
    }

    protected function ensureModelIsAvailable()
    {
        if (empty($this->model)) {
            throw new Exception('Method requires a model to be set on the html builder');
        }
    }
}
