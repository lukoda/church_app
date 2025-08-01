@php
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
@endphp
<x-filament-panels::page>
    {{$this->setCards()}}
    {{$this->setCardPledges()}}
    @php
    $cards = $this->cards;
    $pledges = $this->card_pledges;
    @endphp
    <x-filament::grid
      :default="1"
      :sm="2"
      :md="2"
      :lg="3"
      :xl="3"
      class="gap-2">
        @foreach($cards as $card)
        <x-filament::grid.column class="gap-2">
            <x-filament::section>
                <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                  {{Str::title($card['card_name'])}} 
                </h5>
                
                <ol class="relative border-gray-200 dark:border-gray-700">                  
                    <li class="mb-2">
                        <h3 class="text-md font-semibold text-gray-900 dark:text-white">Card Description</h3>
                        <p class="mb-4 -mt-4 font-normal text-gray-500 dark:text-gray-400 text-xs/[5px] text-wrap">
                          {!! Str::of($card['card_description'])->words('4', '....') !!}
                        </p>
                        <h3 class="text-md font-semibold text-gray-900 dark:text-white">Pledges</h3>
                        <p class="mb-4 font-normal text-gray-500 dark:text-gray-400 text-xs/[5px] text-wrap">Card Target : {{number_format($card['card_target'])}}</p>
                        <p class="mb-4 -mt-4 font-normal text-gray-500 dark:text-gray-400 text-xs/[5px] text-wrap">Member Pledge : 
                          @php
                          $instance=0;
                              foreach($pledges as $pledge){
                                if($pledge['card_id'] == $card['id']){
                                  $instance++;
                                  if($pledge['amount_pledged'] > 0){
                                    echo number_format($pledge['amount_pledged']);
                                  }else{
                                    echo 'No Pledge Registered';
                                  }
                                  break;
                                }
                              }
                              if($instance == 0){
                                echo 'No Pledge Registered';
                              }
                          @endphp
                        </p>
                        <p class="mb-4 -mt-4 font-normal text-gray-500 dark:text-gray-400 text-xs/[5px] text-wrap">Total Offerings : 
                          @php
                          $instance = 0;
                              foreach($pledges as $pledge){
                                if($pledge['card_id'] == $card['id']){
                                  $instance++;
                                  if($pledge['amount_pledged'] > 0){
                                    echo number_format($pledge['amount_completed']);
                                  }else{
                                    echo 'No Pledge Registered';
                                  }
                                  break;
                                }
                              }
                              if($instance == 0){
                                echo 'No Pledge Registered';
                              }
                          @endphp
                        </p>
                        <p class="mb-4 -mt-4 font-normal text-gray-500 dark:text-gray-400 text-xs/[5px] text-wrap">Remain Pledge : 
                          @php
                          $instance=0;
                              foreach($pledges as $pledge){
                                if($pledge['card_id'] == $card['id']){
                                  $instance++;
                                  if($pledge['amount_pledged'] > 0){
                                    echo number_format($pledge['amount_remains']);
                                  }else{
                                    echo 'No Pledge Registered';
                                  }
                                  break;
                                }
                              }
                              if($instance == 0){
                                echo 'No Pledge Registered';
                              }
                          @endphp
                        </p>
                    </li>
                </ol>
                <x-filament-actions::modals />
          </x-filament::section>
        </x-filament::grid.column>
        @endforeach
    </x-filament::grid>
    {{$this->table}}
</x-filament-panels::page>
