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
    $selectedLabel = '';

    foreach ($options as $key => $optVal) {
        if (is_array($optVal)) {
            $val = $optVal['value'] ?? $key;
            $label = $optVal['label'] ?? $val;
            $extraAttr = $optVal['extra'] ?? [];
        } else {
            $val = $key;
            $label = $optVal;
            $extraAttr = [];
        }

        $strVal = (string)$val;
        $strLabel = (string)$label;

        if ((string)$selectedValue === $strVal) {
            $selectedLabel = $strLabel;
        }

        $normalizedOptions[] = [
            'value' => $strVal,
            'label' => $strLabel,
            'extra' => $extraAttr
        ];
    }

    if ($selectedLabel === '' && (string)$selectedValue !== '') {
        $selectedLabel = (string)$selectedValue;
    }

    $displayText = $selectedLabel !== '' ? $selectedLabel : ($placeholder ?: 'Pilih...');
    $isPlaceholderActive = ($selectedLabel === '');
@endphp

<div class="relative custom-select-wrapper w-full {{ $class }}" data-select-id="{{ $elementId }}">
    <!-- Form submission value handled via Hidden Input -->
    <input type="hidden"
           name="{{ $name }}"
           id="{{ $elementId }}"
           value="{{ $selectedValue }}"
           @if($onchange) data-onchange="{{ $onchange }}" @endif
           @if($required) required @endif
           @if($disabled) disabled @endif>

    <!-- Custom Select Trigger Button -->
    <button type="button"
            class="custom-select-trigger relative w-full h-[42px] px-3.5 flex items-center justify-between bg-white border border-[#D9E1E7] rounded-lg text-[13px] font-medium text-slate-800 shadow-2xs focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/15 cursor-pointer disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed transition duration-150 ease-in-out text-left"
            aria-haspopup="listbox"
            aria-expanded="false"
            @if($disabled) disabled @endif>
        <span class="custom-select-label truncate {{ $isPlaceholderActive ? 'text-slate-400 font-normal' : 'text-slate-800 font-semibold' }}">
            {{ $displayText }}
        </span>
        <svg class="custom-select-chevron w-3.5 h-3.5 text-slate-500 transition-transform duration-150 flex-shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <!-- Custom Select Menu Panel -->
    <div class="custom-select-menu hidden absolute left-0 right-0 top-full mt-1 bg-white border border-[#D9E1E7] rounded-lg shadow-lg z-[9999] max-h-60 overflow-y-auto py-1 text-[13px]"
         role="listbox">
        @if($placeholder)
            <div class="custom-select-option px-3.5 py-2 cursor-pointer flex items-center justify-between text-slate-500 hover:bg-slate-50 transition-colors {{ (string)$selectedValue === '' ? 'bg-emerald-50/70 font-semibold text-emerald-900' : '' }}"
                 data-value=""
                 data-label="{{ $placeholder }}"
                 role="option"
                 aria-selected="{{ (string)$selectedValue === '' ? 'true' : 'false' }}">
                <span class="truncate">{{ $placeholder }}</span>
                @if((string)$selectedValue === '')
                    <svg class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                @endif
            </div>
        @endif

        @foreach ($normalizedOptions as $opt)
            @php
                $isSelected = (string)$opt['value'] === (string)$selectedValue;
            @endphp
            <div class="custom-select-option px-3.5 py-2 cursor-pointer flex items-center justify-between text-slate-800 hover:bg-[#F3F7F5] transition-colors {{ $isSelected ? 'bg-emerald-50/80 font-bold text-emerald-950' : 'font-normal' }}"
                 data-value="{{ $opt['value'] }}"
                 data-label="{{ $opt['label'] }}"
                 role="option"
                 aria-selected="{{ $isSelected ? 'true' : 'false' }}"
                 @foreach($opt['extra'] as $eKey => $eVal) data-{{ $eKey }}="{{ $eVal }}" @endforeach>
                <span class="truncate">{{ $opt['label'] }}</span>
                @if($isSelected)
                    <svg class="w-3.5 h-3.5 text-emerald-600 flex-shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                @endif
            </div>
        @endforeach
    </div>
</div>

