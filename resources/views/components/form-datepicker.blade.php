@props([
    'name' => '',
    'id' => null,
    'value' => '',
    'placeholder' => 'DD/MM/YYYY',
    'disabled' => false,
    'required' => false,
    'onchange' => null,
    'class' => '',
])

@php
    $elementId = $id ?? ($name ? $name . '_' . Str::random(6) : 'date_' . Str::random(6));
    $rawValue = old($name, $value);
    
    // Format YYYY-MM-DD or DD/MM/YYYY into display and raw format
    $formattedDisplay = '';
    $formattedIso = '';

    if (!empty($rawValue)) {
        try {
            $dateObj = \Carbon\Carbon::parse($rawValue);
            $formattedIso = $dateObj->format('Y-m-d');
            $formattedDisplay = $dateObj->format('d/m/Y');
        } catch (\Exception $e) {
            $formattedIso = (string)$rawValue;
            $formattedDisplay = (string)$rawValue;
        }
    }
@endphp

<div class="relative custom-datepicker-wrapper w-full {{ $class }}" data-datepicker-id="{{ $elementId }}">
    <!-- Hidden input for ISO backend submission (YYYY-MM-DD) -->
    <input type="hidden"
           name="{{ $name }}"
           id="{{ $elementId }}"
           value="{{ $formattedIso }}"
           @if($onchange) data-onchange="{{ $onchange }}" @endif
           @if($required) required @endif
           @if($disabled) disabled @endif>

    <!-- Trigger Input Box (Displays DD/MM/YYYY format) -->
    <div class="relative">
        <input type="text"
               readonly
               class="custom-datepicker-trigger w-full h-[42px] pl-3.5 pr-9 bg-white border border-[#D9E1E7] rounded-lg text-[13px] font-medium text-slate-800 shadow-2xs focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/15 cursor-pointer disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed transition duration-150 ease-in-out"
               placeholder="{{ $placeholder }}"
               value="{{ $formattedDisplay }}"
               aria-haspopup="dialog"
               aria-expanded="false"
               @if($disabled) disabled @endif>
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
        </div>
    </div>

    <!-- Floating Calendar Popover Panel -->
    <div class="custom-datepicker-popover hidden absolute left-0 top-full mt-1 bg-white border border-[#D9E1E7] rounded-xl shadow-lg z-[9999] p-3.5 w-72 text-slate-800 text-xs select-none"
         role="dialog"
         aria-modal="true">
        
        <!-- Header: Month/Year navigation -->
        <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
            <span class="custom-datepicker-monthyear font-bold text-sm text-slate-900"></span>
            <div class="flex items-center gap-1">
                <button type="button" class="custom-datepicker-prev p-1 rounded-md text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition focus:outline-none" aria-label="Bulan Sebelumnya">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </button>
                <button type="button" class="custom-datepicker-next p-1 rounded-md text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition focus:outline-none" aria-label="Bulan Berikutnya">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
            </div>
        </div>

        <!-- Day Names Header Grid -->
        <div class="grid grid-cols-7 gap-1 text-center font-bold text-[11px] text-slate-400 mb-1.5">
            <div>Min</div>
            <div>Sen</div>
            <div>Sel</div>
            <div>Rab</div>
            <div>Kam</div>
            <div>Jum</div>
            <div>Sab</div>
        </div>

        <!-- Calendar Days Grid -->
        <div class="custom-datepicker-days grid grid-cols-7 gap-1 text-center font-medium"></div>

        <!-- Footer Actions -->
        <div class="flex items-center justify-between pt-2.5 mt-2.5 border-t border-slate-100 text-[11px] font-semibold">
            <button type="button" class="custom-datepicker-clear text-rose-600 hover:text-rose-700 transition focus:outline-none">Hapus</button>
            <button type="button" class="custom-datepicker-today text-emerald-700 hover:text-emerald-800 transition focus:outline-none">Hari ini</button>
        </div>
    </div>
</div>
