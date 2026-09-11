@props([
    'name' => '',
    'id' => null,
    'value' => '',
    'options' => [],
    'placeholder' => 'Pilih...',
    'disabled' => false,
    'required' => false,
    'onchange' => null,
    'class' => '',
])

@php
    $elementId = $id ?? ($name ? $name . '_' . Str::random(6) : 'select_' . Str::random(6));
    $selectedValue = old($name, $value);

    // Normalize options list into array of key-value pairs
    $normalizedOptions = [];
    foreach ($options as $key => $optVal) {
        if (is_array($optVal)) {
            $val = $optVal['value'] ?? $key;
            $label = $optVal['label'] ?? $val;
            $extraAttr = $optVal['extra'] ?? [];
        } else {
            // If associative array with string keys or sequential array
            if (is_numeric($key) && !is_array($options)) {
                $val = $optVal;
                $label = $optVal;
            } else {
                $val = is_numeric($key) ? $optVal : $key;
                $label = $optVal;
            }
            $extraAttr = [];
        }
        $normalizedOptions[] = [
            'value' => (string)$val,
            'label' => (string)$label,
            'extra' => $extraAttr
        ];
    }

    // Find current label
    $currentLabel = $placeholder;
    foreach ($normalizedOptions as $opt) {
        if ((string)$opt['value'] === (string)$selectedValue) {
            $currentLabel = $opt['label'];
            break;
        }
    }
@endphp

<div class="relative custom-select-wrapper w-full {{ $class }}"
     id="wrapper-{{ $elementId }}"
     data-disabled="{{ $disabled ? 'true' : 'false' }}"
     data-required="{{ $required ? 'true' : 'false' }}">

    <!-- Hidden Native Input for standard HTML Form submission & validation -->
    <input type="hidden"
           name="{{ $name }}"
           id="{{ $elementId }}"
           value="{{ $selectedValue }}"
           @if($required) required @endif
           @if($disabled) disabled @endif
           @if($onchange) onchange="{{ $onchange }}" @endif>

    <!-- Trigger Button -->
    <button type="button"
            id="trigger-{{ $elementId }}"
            class="custom-select-trigger w-full h-[44px] px-3.5 bg-white border border-slate-200 rounded-[10px] text-sm text-slate-800 flex items-center justify-between shadow-sm transition duration-150 ease-in-out focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 {{ $disabled ? 'bg-slate-100 border-slate-200 text-slate-400 cursor-not-allowed opacity-80' : 'hover:border-slate-300 cursor-pointer' }}"
            aria-haspopup="listbox"
            aria-expanded="false"
            aria-controls="panel-{{ $elementId }}"
            @if($disabled) disabled @endif>
        <span class="custom-select-label truncate font-medium text-[14px]">
            {{ $currentLabel }}
        </span>
        <svg class="custom-select-arrow w-4 h-4 ml-2 text-slate-400 transition-transform duration-200 ease-in-out flex-shrink-0"
             fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <!-- Floating Options Dropdown Panel -->
    <div id="panel-{{ $elementId }}"
         class="custom-select-panel hidden absolute left-0 right-0 top-full mt-1.5 bg-white border border-slate-200 rounded-[12px] shadow-lg shadow-slate-900/10 z-[9999] max-h-72 overflow-y-auto py-1 focus:outline-none transition-all duration-150 ease-in-out transform opacity-0 -translate-y-1 scale-[0.99]"
         tabindex="-1"
         role="listbox">
        @foreach ($normalizedOptions as $index => $opt)
            @php
                $isSelected = (string)$opt['value'] === (string)$selectedValue;
            @endphp
            <div role="option"
                 id="opt-{{ $elementId }}-{{ $index }}"
                 aria-selected="{{ $isSelected ? 'true' : 'false' }}"
                 data-value="{{ $opt['value'] }}"
                 data-label="{{ $opt['label'] }}"
                 @foreach($opt['extra'] as $eKey => $eVal) data-{{ $eKey }}="{{ $eVal }}" @endforeach
                 class="custom-select-option h-[40px] px-3.5 text-[14px] flex items-center justify-between cursor-pointer select-none transition-colors duration-150 {{ $isSelected ? 'bg-emerald-50 text-emerald-900 font-semibold' : 'text-slate-700 hover:bg-emerald-50/70 hover:text-slate-900' }}">
                <span class="truncate">{{ $opt['label'] }}</span>
                <span class="custom-select-check flex-shrink-0 ml-2 text-emerald-600 {{ $isSelected ? 'block' : 'hidden' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </span>
            </div>
        @endforeach
    </div>
</div>
