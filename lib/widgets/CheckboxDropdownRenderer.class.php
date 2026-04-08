<?php

/**
 * Shared rendering logic for checkbox dropdown widgets.
 */
class CheckboxDropdownRenderer
{
    /**
     * Renders a compact dropdown with checkboxes.
     *
     * @param string $id       Widget HTML id
     * @param string $name     Form field name (with [] for multiple)
     * @param array  $choices  Key-value pairs of choices
     * @param array  $selectedValues Currently selected values
     * @param string $width    CSS min-width
     * @param string $placeholder Button label when nothing selected
     * @param string $maxHeight CSS max-height for the list
     * @return string HTML
     */
    public static function render($id, $name, $choices, $selectedValues, $width, $placeholder, $maxHeight)
    {
        // Remove empty choice
        unset($choices['']);
        unset($choices[null]);

        // Normalize selected values: handle single values, old filter format, etc.
        if (!is_array($selectedValues)) {
            $selectedValues = ($selectedValues !== '' && $selectedValues !== null) ? array($selectedValues) : array();
        }
        // Handle old sfWidgetFormFilterInput format: array('text' => 'value')
        if (isset($selectedValues['text'])) {
            $selectedValues = ($selectedValues['text'] !== '' && $selectedValues['text'] !== null) ? array($selectedValues['text']) : array();
        }
        $selectedStrings = array_map('strval', array_values($selectedValues));

        // Build checkbox list HTML
        // Append [] to name for array submission if not already present
        $arrayName = substr($name, -2) === '[]' ? $name : $name . '[]';
        $checkboxesHtml = '';
        foreach ($choices as $choiceValue => $choiceLabel) {
            $checked = in_array((string)$choiceValue, $selectedStrings, true) ? 'checked' : '';
            $escapedLabel = htmlspecialchars($choiceLabel, ENT_QUOTES, 'UTF-8');
            $escapedValue = htmlspecialchars($choiceValue, ENT_QUOTES, 'UTF-8');
            $checkboxesHtml .= sprintf(
                '<label class="cbd-item" data-search="%s">'
                . '<input type="checkbox" name="%s" value="%s" %s />'
                . '<span>%s</span>'
                . '</label>',
                mb_strtolower($escapedLabel, 'UTF-8'),
                $arrayName,
                $escapedValue,
                $checked,
                $escapedLabel
            );
        }

        $selectedCount = count($selectedStrings);
        $buttonLabel = $selectedCount > 0 ? $selectedCount . ' sélectionné(s)' : $placeholder;
        $activeClass = $selectedCount > 0 ? ' cbd-trigger--active' : '';
        $escapedPlaceholder = htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<div class="cbd-wrapper" id="{$id}_cbd" style="display:inline-block;position:relative;vertical-align:middle;">
    <button type="button" class="cbd-trigger{$activeClass}" style="min-width:{$width};">
        <span class="cbd-label">{$buttonLabel}</span>
        <span class="cbd-arrow">&#9662;</span>
    </button>
    <div class="cbd-dropdown" style="min-width:{$width};display:none;">
        <div class="cbd-search-wrap">
            <input type="text" class="cbd-search" placeholder="Rechercher..." autocomplete="off" />
        </div>
        <div class="cbd-list" style="max-height:{$maxHeight};overflow-y:auto;">
            {$checkboxesHtml}
        </div>
        <div class="cbd-no-results" style="display:none;padding:8px 12px;color:#999;font-size:12px;">Aucun résultat</div>
    </div>
</div>

<script type="text/javascript">
(function() {
    var wrapper = document.getElementById('{$id}_cbd');
    if (!wrapper) return;

    var trigger = wrapper.querySelector('.cbd-trigger');
    var dropdown = wrapper.querySelector('.cbd-dropdown');
    var search = wrapper.querySelector('.cbd-search');
    var items = wrapper.querySelectorAll('.cbd-item');
    var noResults = wrapper.querySelector('.cbd-no-results');
    var label = wrapper.querySelector('.cbd-label');
    var placeholder = '{$escapedPlaceholder}';

    function updateLabel() {
        var checked = wrapper.querySelectorAll('.cbd-item input:checked');
        if (checked.length > 0) {
            label.textContent = checked.length + ' sélectionné(s)';
            trigger.classList.add('cbd-trigger--active');
        } else {
            label.textContent = placeholder;
            trigger.classList.remove('cbd-trigger--active');
        }
    }

    trigger.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var isOpen = dropdown.style.display !== 'none';
        closeAllDropdowns();
        if (!isOpen) {
            dropdown.style.display = 'block';
            search.focus();
        }
    });

    search.addEventListener('input', function() {
        var query = this.value.toLowerCase();
        var visibleCount = 0;
        for (var i = 0; i < items.length; i++) {
            var text = items[i].getAttribute('data-search');
            var match = !query || text.indexOf(query) !== -1;
            items[i].style.display = match ? '' : 'none';
            if (match) visibleCount++;
        }
        noResults.style.display = visibleCount === 0 ? 'block' : 'none';
    });

    search.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    dropdown.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    for (var i = 0; i < items.length; i++) {
        items[i].querySelector('input').addEventListener('change', function() {
            updateLabel();
        });
    }

    function closeAllDropdowns() {
        var all = document.querySelectorAll('.cbd-dropdown');
        for (var j = 0; j < all.length; j++) {
            all[j].style.display = 'none';
        }
    }

    document.addEventListener('click', function() {
        dropdown.style.display = 'none';
    });
})();
</script>
HTML;
    }
}
