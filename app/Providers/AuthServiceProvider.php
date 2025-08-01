<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\BeneficiaryRequest;
use App\Models\Booking;
use App\Models\Card;
use App\Models\CardPledge;
use App\Models\Church;
use App\Models\ChurchMember;
use App\Models\ChurchSecretary;
use App\Models\Committee;
use App\Models\Contribution;
use App\Models\Dependant;
use App\Models\Dinomination;
use App\Models\Diocese;
use App\Models\District;
use App\Models\IntroductionNote;
use App\Models\Jumuiya;
use App\Models\JumuiyaAccountant;
use App\Models\JumuiyaChairPerson;
use App\Models\JumuiyaMember;
use App\Models\JumuiyaRevenue;
use App\Models\LogContribution;
use App\Models\Offering;
use App\Models\PastorSchedule;
use App\Models\Pledge;
use App\Models\PledgePayment;
use App\Models\Region;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\ServiceOffering;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Ward;
use App\Models\Admin;
use App\Policies\AnnouncementPolicy;
use App\Policies\AttendancePolicy;
use App\Policies\BeneficiaryRequestPolicy;
use App\Policies\BookingPolicy;
use App\Policies\CardPledgePolicy;
use App\Policies\ChurchSecretaryPolicy;
use App\Policies\CardPolicy;
use App\Policies\ChurchMemberPolicy;
use App\Policies\ChurchPolicy;
use App\Policies\CommitteePolicy;
use App\Policies\ContributionPolicy;
use App\Policies\DependantPolicy;
use App\Policies\DinominationPolicy;
use App\Policies\DiocesePolicy;
use App\Policies\DistrictPolicy;
use App\Policies\IntroductionNotePolicy;
use App\Policies\JumuiyaAccountantPolicy;
use App\Policies\JumuiyaChairPersonPolicy;
use App\Policies\JumuiyaMemberPolicy;
use App\Policies\JumuiyaPolicy;
use App\Policies\JumuiyaRevenuePolicy;
use App\Policies\LogContributionPolicy;
use App\Policies\OfferingPolicy;
use App\Policies\PastorSchedulePolicy;
use App\Policies\PledgePaymentPolicy;
use App\Policies\PledgePolicy;
use App\Policies\RegionPolicy;
use App\Policies\RolePolicy;
use App\Policies\SchedulePolicy;
use App\Policies\ServiceOfferingPolicy;
use App\Policies\UserPolicy;
use App\Policies\UserRolePolicy;
use App\Policies\WardPolicy;
use Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Announcement::class       => AnnouncementPolicy::class,
        Attendance::class         => AttendancePolicy::class,
        BeneficiaryRequest::class => BeneficiaryRequestPolicy::class,
        Booking::class            => BookingPolicy::class,
        Card::class               => CardPolicy::class,
        CardPledge::class         => CardPledgePolicy::class,
        Church::class             => ChurchPolicy::class,
        ChurchMember::class       => ChurchMemberPolicy::class,
        ChurchSecretary::class    => ChurchSecretaryPolicy::class,
        Committee::class          => CommitteePolicy::class,
        Contribution::class       => ContributionPolicy::class,
        Dependant::class          => DependantPolicy::class,
        Dinomination::class       => DinominationPolicy::class,
        Diocese::class            => DiocesePolicy::class,
        District::class           => DistrictPolicy::class,
        IntroductionNote::class   => IntroductionNotePolicy::class,
        Jumuiya::class            => JumuiyaPolicy::class,
        JumuiyaAccountant::class  => JumuiyaAccountantPolicy::class,
        JumuiyaChairPerson::class => JumuiyaChairPersonPolicy::class,
        JumuiyaMember::class      => JumuiyaMemberPolicy::class,
        JumuiyaRevenue::class     => JumuiyaRevenuePolicy::class,
        LogContribution::class    => LogContributionPolicy::class,
        Offering::class           => OfferingPolicy::class,
        PastorSchedule::class     => PastorSchedulePolicy::class,
        Pledge::class             => PledgePolicy::class,
        PledgePayment::class      => PledgePaymentPolicy::class,
        Region::class             => RegionPolicy::class,
        Role::class               => RolePolicy::class,
        Schedule::class           => SchedulePolicy::class,
        ServiceOffering::class    => ServiceOfferingPolicy::class,
        User::class               => UserPolicy::class,
        UserRole::class           => UserRolePolicy::class,
        Ward::class               => WardPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::before(function (User|Admin $user, string $ability) {
            return $user->hasRole('System Admin') ? true: null;
        });
    }
}
