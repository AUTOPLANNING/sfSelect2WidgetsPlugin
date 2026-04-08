<?php

require_once(dirname(__FILE__) . '/CheckboxDropdownRenderer.class.php');

/**
 * Compact dropdown with checkboxes for multiselect filtering (static choices).
 * Renders as a button that opens an overlay dropdown with search and checkboxes.
 */
class sfWidgetFormCheckboxDropdownChoice extends sfWidgetFormChoice
{
    protected function configure($options = array(), $attributes = array())
    {
        $this->addOption('width', '200px');
        $this->addOption('placeholder', 'Sélectionnez...');
        $this->addOption('max_height', '250px');

        parent::configure($options, $attributes);

        $this->setOption('multiple', true);
    }

    public function render($name, $value = null, $attributes = array(), $errors = array())
    {
        $id = $this->generateId($name);
        $choices = $this->getChoices();
        $selectedValues = is_array($value) ? $value : ($value ? array($value) : array());

        return CheckboxDropdownRenderer::render(
            $id,
            $name,
            $choices,
            $selectedValues,
            $this->getOption('width'),
            __($this->getOption('placeholder')),
            $this->getOption('max_height')
        );
    }

    public function getStylesheets()
    {
        return array('/css/checkbox-dropdown.css' => 'all');
    }

    public function getJavascripts()
    {
        return array();
    }
}
