@props([
    'name' => 'prodi',
    'id' => null,
    'value' => '',
    'fakultasData' => [],
    'placeholder' => 'Semua Program Studi',
    'disabled' => false,
    'required' => false,
    'onchange' => null,
    'class' => '',
])

@php
    $elementId = $id ?? ($name ? $name . '_' . Str::random(6) : 'prodi_select_' . Str::random(6));
    $selectedValue = (string) old($name, $value);

    // Resolve current selected label
    $selectedLabel = '';
    if ($selectedValue !== '') {
        foreach ($fakultasData as $fakultas) {
            foreach ($fakultas->programStudi as $prodi) {
                if ((string)$prodi->id === $selectedValue || (string)$prodi->nama === $selectedValue) {
                    $selectedLabel = $prodi->nama;
                    break 2;
                }
            }
        }
        if ($selectedLabel === '') {
            $selectedLabel = $selectedValue;
        }
    }

    $displayText = $selectedLabel !== '' ? $selectedLabel : $placeholder;
    $isPlaceholderActive = ($selectedLabel === '');
@endphp

<div class="relative custom-select-wrapper custom-prodi-select-wrapper w-full {{ $class }}" data-select-id="{{ $elementId }}">
    <!-- Hidden input for form submission -->
    <input type="hidden"
           name="{{ $name }}"
           id="{{ $elementId }}"
           value="{{ $selectedValue }}"
           @if($onchange) data-onchange="{{ $onchange }}" @endif
           @if($required) required @endif
           @if($disabled) disabled @endif>

    <!-- Custom Select Trigger Button -->
    <button type="button"
            class="custom-select-trigger relative w-full h-[44px] px-3.5 flex items-center justify-between bg-white border border-[#D9E1E7] rounded-xl text-[13px] font-semibold text-slate-800 shadow-2xs focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/15 cursor-pointer disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed transition duration-150 ease-in-out text-left"
            aria-haspopup="listbox"
            aria-expanded="false"
            @if($disabled) disabled @endif>
        <span class="custom-select-label truncate {{ $isPlaceholderActive ? 'text-slate-400 font-normal' : 'text-slate-800 font-semibold' }}">
            {{ $displayText }}
        </span>
        <svg class="custom-select-chevron w-4 h-4 text-slate-500 transition-transform duration-150 flex-shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <!-- Custom Select Menu Panel -->
    <div class="custom-select-menu hidden absolute left-0 right-0 top-full mt-1.5 bg-white border border-[#E5E7EB] rounded-xl shadow-xl z-[9999] max-h-[380px] overflow-y-auto py-0 text-[13px]"
         role="listbox">
        
        <!-- Sticky Search Box inside Dropdown Panel (NO EMOJIS) -->
        <div class="p-2.5 bg-white border-b border-slate-100 sticky top-0 z-20 shadow-2xs">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text"
                       class="custom-prodi-search-input w-full h-[38px] pl-9 pr-3 text-[13px] bg-white border border-[#D8E0E8] rounded-lg focus:outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/15 text-slate-800 placeholder-slate-400 font-medium"
                       placeholder="Cari program studi..."
                       autocomplete="off">
            </div>
        </div>

        <!-- Default "Semua Program Studi" Option -->
        <div class="custom-select-option custom-prodi-option h-[42px] px-4 cursor-pointer flex items-center justify-between text-slate-800 hover:bg-[#F3F7F5] transition-colors border-b border-slate-100/70 {{ $selectedValue === '' ? 'bg-emerald-50/80 font-bold text-slate-900' : 'font-semibold' }}"
             data-value=""
             data-label="{{ $placeholder }}"
             data-search-text="{{ strtolower($placeholder) }}"
             role="option"
             aria-selected="{{ $selectedValue === '' ? 'true' : 'false' }}">
            <span class="truncate">{{ $placeholder }}</span>
            @if($selectedValue === '')
                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            @endif
        </div>

        <!-- Grouped List by Fakultas -->
        @foreach($fakultasData as $fakultas)
            @if($fakultas->programStudi->count() > 0)
                <div class="custom-prodi-group-header px-4 py-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider bg-slate-50/95 border-y border-slate-100 sticky top-[54px] z-10 select-none cursor-default"
                     data-fakultas-id="{{ $fakultas->id }}">
                    {{ $fakultas->kode }} <span class="text-slate-300 mx-1">·</span> {{ $fakultas->nama }}
                </div>

                @foreach($fakultas->programStudi as $prodi)
                    @php
                        $isCurrent = ($selectedValue === (string)$prodi->id || $selectedValue === (string)$prodi->nama);
                    @endphp
                    <div class="custom-select-option custom-prodi-option py-2.5 px-4 cursor-pointer flex items-center justify-between text-slate-800 hover:bg-[#F3F7F5] transition-colors {{ $isCurrent ? 'bg-emerald-50/80 font-bold text-slate-900' : 'font-normal' }}"
                         data-value="{{ $prodi->id }}"
                         data-nama="{{ $prodi->nama }}"
                         data-label="{{ $prodi->nama }}"
                         data-fakultas-id="{{ $fakultas->id }}"
                         data-search-text="{{ strtolower($prodi->nama) }}"
                         role="option"
                         aria-selected="{{ $isCurrent ? 'true' : 'false' }}">
                        <span class="truncate pl-0.5">{{ $prodi->nama }}</span>
                        @if($isCurrent)
                            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        @endif
                    </div>
                @endforeach
            @endif
        @endforeach

        <!-- Empty search results placeholder (NO EMOJIS) -->
        <div class="custom-prodi-no-results hidden p-6 text-center text-xs text-slate-400">
            <svg class="w-6 h-6 mx-auto mb-1.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <span>Tidak ada program studi yang ditemukan.</span>
        </div>
    </div>
</div>
