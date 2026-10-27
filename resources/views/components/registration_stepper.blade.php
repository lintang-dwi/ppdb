<!-- Stepper Indicator Component: Minimalist 4-Step Circles + Connecting Lines -->
<div class="mb-6 sm:mb-8" aria-label="Tahapan Pendaftaran">
    <div class="flex items-center justify-between max-w-md mx-auto">
        @php
            $wizardSteps = [
                1 => 'Jalur',
                2 => 'Data Siswa',
                3 => 'Info Tambahan',
                4 => 'Berkas',
            ];
        @endphp
        @foreach ($wizardSteps as $num => $title)
            <div class="flex items-center {{ $num < 4 ? 'flex-1' : '' }}">
                <div class="flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-200"
                         :class="step > {{ $num }} ? 'bg-emerald-700 text-white' : (step === {{ $num }} ? 'bg-emerald-700 text-white ring-4 ring-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-300')">
                        <template x-if="step > {{ $num }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </template>
                        <template x-if="step <= {{ $num }}">
                            <span>{{ $num }}</span>
                        </template>
                    </div>
                    <span class="text-2xs font-semibold mt-1 text-center hidden sm:block"
                          :class="step >= {{ $num }} ? 'text-emerald-800' : 'text-slate-500'">{{ $title }}</span>
                </div>
                @if ($num < 4)
                    <div class="flex-1 h-0.5 mx-2 -mt-4 sm:-mt-5 transition-colors duration-200"
                         :class="step > {{ $num }} ? 'bg-emerald-600' : 'bg-slate-200'"></div>
                @endif
            </div>
        @endforeach
    </div>
</div>

