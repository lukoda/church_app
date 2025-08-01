<?php

namespace App\Livewire;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\BeneficiaryRequestItemPayment;
use App\Models\BeneficiaryRequestItemPledge;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use DB;
use Filament\Forms\Components\Grid;

class BeneficiaryRequestsOverview extends BaseWidget
{
    public int $record;

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $total_pledges = DB::table('beneficiary_request_item_pledges')->join('beneficiary_request_items', 'beneficiary_request_item_pledges.pledged_item_id', '=', 'beneficiary_request_items.id')
                                    ->join('beneficiary_requests', 'beneficiary_request_items.beneficiary_request_id', '=', 'beneficiary_requests.id')->where('beneficiary_requests.id', $this->record)->sum('item_quantity_pledged');

        $total_completed_pledges = DB::table('beneficiary_request_item_pledges')->join('beneficiary_request_items', 'beneficiary_request_item_pledges.pledged_item_id', '=', 'beneficiary_request_items.id')
                                    ->join('beneficiary_requests', 'beneficiary_request_items.beneficiary_request_id', '=', 'beneficiary_requests.id')
                                    ->join('beneficiary_request_item_payments', 'beneficiary_request_item_pledges.id', '=', 'beneficiary_request_item_payments.item_id')->where('beneficiary_requests.id', $this->record)
                                    ->whereIn('verification_status', ['verified', 'partial paid'])->sum ('item_quantity_verified');

        $total_pending_pledges = BeneficiaryRequestItemPayment::whereRelation('beneficiary_request_item.beneficiary_request_item.beneficiary_request', 'id','=', $this->record)->with('beneficiary_request_item')
                                    ->where('verification_status', 'unverified')->sum('item_quantity_payed');

        $total_amount_pledges = BeneficiaryRequestItemPledge::whererelation('beneficiary_request', 'id', '=', $this->record)->sum('amount_pledged');

        $total_verified_amount = BeneficiaryRequestItemPayment::whererelation('beneficiary_request_item.beneficiary_request', 'id', '=', $this->record)->whereIn('verification_status', ['verified', 'partial paid'])->sum('amount_payed_verified');

        $total_pending_amounts = BeneficiaryRequestItemPayment::whererelation('beneficiary_request_item.beneficiary_request', 'id', '=', $this->record)->where('verification_status', 'unverified')->sum('amount_payed');

        return [
                    Stat::make('Total Item Pledges', number_format($total_pledges))
                        ->description('Total pledged items')
                        ->color('secondary'),
                    Stat::make('Total Completed Item Pledges', number_format($total_completed_pledges))
                        ->description('Total verified pledge item payments.')
                        ->color('success'),
                    Stat::make('Total Pending Item Pledges', number_format($total_pending_pledges))
                        ->description('Total unverified pledge item payments.')
                        ->color('danger'),
                    Stat::make('Total Amount Pledged', number_format($total_amount_pledges))
                        ->description('Total amount pledged..')
                        ->color('secondary'),
                    Stat::make('Total Amount Received', number_format($total_verified_amount))
                        ->description('Total amount verified..')
                        ->color('success'),
                    Stat::make('Total Pending Amount Pledges', number_format($total_pending_amounts))
                        ->description('Total amount awaiting verifiication..')
                        ->color('danger'),
        ];
    }
}
