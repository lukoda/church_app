<x-filament-panels::page>
{{$this->setChurchServices()}}
@php
$ChurchServices = $this->church_services;
@endphp
<h3 class="fi-section-header-heading text-xl  font-semibold leading-6 text-gray-950 dark:text-white"> 
    Available Church Services
</h3>
<x-filament::grid
      :default="1"
      :sm="2"
      :md="2"
      :lg="3"
      :xl="3"
      class="gap-2">
      @foreach($ChurchServices as $service)
        <x-filament::grid.column class="gap-2">
            <x-filament::section>
                <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                  {{Str::title($service['title'])}} 
                </h5>
                <ol class="relative border-gray-200 dark:border-gray-700">                  
                    <li class="mb-2">
                        <h3 class="text-md font-semibold text-gray-900 dark:text-white">Service Description</h3>
                        <p class="mb-4 -mt-4 font-normal text-gray-500 dark:text-gray-400 text-xs/[5px] text-wrap">{!! $service['description'] !!}</p>
                    </li>
                </ol>
            </x-filament::section>
        </x-filament::grid.column>
      @endforeach
</x-filament::grid>
{{$this->table}}
</x-filament-panels::page>

