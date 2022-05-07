<?php

namespace Alexwenzel\AjaxSelect;

use Laravel\Nova\Fields\Field;

class AjaxSelect extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'ajax-select';

    public $showOnIndex = false;

    public $showOnDetail = false;

    /**
     * @param $endpoint
     * @return $this
     */
    public function get($endpoint): static
    {
        $this->withMeta(['endpoint' => $endpoint]);

        return $this;
    }

    /**
     * @param $attribute
     * @return $this
     */
    public function parent($attribute): static
    {
        $this->withMeta(['parent_attribute' => $attribute]);

        return $this;
    }
}
