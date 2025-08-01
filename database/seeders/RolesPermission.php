<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

class RolesPermission extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Role::create(['name' => 'System Admin']);
        $dinomination_admin = Role::create(['guard_name' => 'admin', 'name' => 'Dinomination Admin']);
        $diocese_admin = Role::create(['guard_name' => 'admin', 'name' => 'Diocese Admin']);
        $churchdistrict_admin = Role::create(['guard_name' => 'admin', 'name' => 'ChurchDistrict Admin']);
        $parish_admin = Role::create(['guard_name' => 'admin', 'name' => 'Parish Admin']);
        $archbishop = Role::create(['name' => 'ArchBishop']);
        $bishop = Role::create(['name' => 'Bishop']);
        $churchdistrict_pastor = Role::create(['name' => 'ChurchDistrict Pastor']);
        $senior_pastor = Role::create(['name' => 'Senior Pastor']);
        $pastor = Role::create(['name' => 'Pastor']);
        $church_secretary = Role::create(['name' => 'Church Secretary']);
        $subparish_pastor = Role::create(['name' => 'SubParish Pastor']);
        $jumuiya_chairperson = Role::create(['name' => 'Jumuiya Chairperson']);
        $jumuiya_accountant = Role::create(['name' => 'Jumuiya Accountant']);
        $committee_member = Role::create(['name' => 'Committee Member']);
        $guest = Role::create(['name' => 'Guest']);
        $church_member = Role::create(['name' => 'Church Member']);
        $beneficiary = Role::create(['name' => 'Beneficiary']);

        $admin_permissions = [
            'view-any ChurchDistrict',
            'view ChurchDistrict',
            'create ChurchDistrict',
            'update ChurchDistrict',
            'delete ChurchDistrict',
            'restore ChurchDistrict',
            'force-delete ChurchDistrict',
            'view-any Church',
            'view Church',
            'create Church',
            'update Church',
            'delete Church',
            'restore Church',
            'force-delete Church',
            'view-any Diocese',
            'view Diocese',
            'create Diocese',
            'update Diocese',
            'delete Diocese',
            'restore Diocese',
            'force-delete Diocese',
            'view-any Dinomination',
            'view Dinomination',
            'create Dinomination',
            'update Dinomination',
            'delete Dinomination',
            'restore Dinomination',
            'force-delete Dinomination',
            'view-any Pastor',
            'view Pastor',
            'create Pastor',
            'update Pastor',
            'delete Pastor',
            'view-any ChurchSecretary',
            'view ChurchSecretary',
            'create ChurchSecretary',
            'update ChurchSecretary',
            'delete ChurchSecretary',
            // 'restore Pastor',
            // 'force-delete Pastor',
            // 'assign Pastor',
        ];

        $permissions = [
            'view-any AdhocOffering',
            'view AdhocOffering',
            'create AdhocOffering',
            'update AdhocOffering',
            'delete AdhocOffering',
            'restore AdhocOffering',
            'force-delete AdhocOffering',
            'deactivate AdhocOffering',
            'view-any Announcement',
            'view Announcement',
            'create Announcement',
            'update Announcement',
            'delete Announcement',
            'restore Announcement',
            'force-delete Announcement',
            'view-any AnnouncementPublications',
            'view AnnouncementPublications',
            'create AnnouncementPublications',
            'update AnnouncementPublications',
            'delete AnnouncementPublications',
            'restore AnnouncementPublications',
            'force-delete AnnouncementPublications',
            'view-any Attendance',
            'view Attendance',
            'create Attendance',
            'update Attendance',
            'delete Attendance',
            'restore Attendance',
            'force-delete Attendance',
            'view-any AuctionItemPayment',
            'view AuctionItemPayment',
            'create AuctionItemPayment',
            'update AuctionItemPayment',
            'delete AuctionItemPayment',
            'restore AuctionItemPayment',
            'force-delete AuctionItemPayment',
            'view-any AuctionItem',
            'view AuctionItem',
            'create AuctionItem',
            'update AuctionItem',
            'delete AuctionItem',
            'restore AuctionItem',
            'force-delete AuctionItem',
            'view-any Beneficiary',
            'view Beneficiary',
            'create Beneficiary',
            'update Beneficiary',
            'delete Beneficiary',
            'restore Beneficiary',
            'force-delete Beneficiary',
            'deactivate Beneficiary',
            'view-any BeneficiaryRequestItem',
            'view BeneficiaryRequestItem',
            'create BeneficiaryRequestItem',
            'update BeneficiaryRequestItem',
            'delete BeneficiaryRequestItem',
            'restore BeneficiaryRequestItem',
            'force-delete BeneficiaryRequestItem',
            'view-any BeneficiaryRequest',
            'view BeneficiaryRequest',
            'create BeneficiaryRequest',
            'update BeneficiaryRequest',
            'delete BeneficiaryRequest',
            'restore BeneficiaryRequest',
            'force-delete BeneficiaryRequest',
            'deactivate BeneficiaryRequest',
            'view-any BeneficiaryRequestItemPayment',
            'view BeneficiaryRequestItemPayment',
            'create BeneficiaryRequestItemPayment',
            'update BeneficiaryRequestItemPayment',
            'delete BeneficiaryRequestItemPayment',
            'restore BeneficiaryRequestItemPayment',
            'force-delete BeneficiaryRequestItemPayment',
            'verify BeneficiaryRequestPayments',
            'view-any BeneficiaryRequestItemPledge',
            'view BeneficiaryRequestItemPledge',
            'create BeneficiaryRequestItemPledge',
            'update BeneficiaryRequestItemPledge',
            'delete BeneficiaryRequestItemPledge',
            'restore BeneficiaryRequestItemPledge',
            'force-delete BeneficiaryRequestItemPledge',
            'view-any Booking',
            'view Booking',
            'create Booking',
            'update Booking',
            'delete Booking',
            'restore Booking',
            'force-delete Booking',
            'approve Booking',
            'view-any CardPledge',
            'view CardPledge',
            'create CardPledge',
            'update CardPledge',
            'delete CardPledge',
            'restore CardPledge',
            'force-delete CardPledge',
            'view-any Card',
            'view Card',
            'create Card',
            'update Card',
            'delete Card',
            'restore Card',
            'force-delete Card',
            'view-any ChurchAuction',
            'view ChurchAuction',
            'create ChurchAuction',
            'update ChurchAuction',
            'delete ChurchAuction',
            'restore ChurchAuction',
            'force-delete ChurchAuction',
            'view-any ChurchDistrict',
            'view ChurchDistrict',
            'create ChurchDistrict',
            'update ChurchDistrict',
            'delete ChurchDistrict',
            'restore ChurchDistrict',
            'force-delete ChurchDistrict',
            'view-any ChurchMass',
            'view ChurchMass',
            'create ChurchMass',
            'update ChurchMass',
            'delete ChurchMass',
            'restore ChurchMass',
            'force-delete ChurchMass',
            'view-any ChurchMember',
            'view ChurchMember',
            'create ChurchMember',
            'update ChurchMember',
            'delete ChurchMember',
            'restore ChurchMember',
            'force-delete ChurchMember',
            'verify ChurchMember',
            'view-any Church',
            'view Church',
            'create Church',
            'update Church',
            'delete Church',
            'restore Church',
            'force-delete Church',
            'view-any ChurchSecretary',
            'view ChurchSecretary',
            'create ChurchSecretary',
            'update ChurchSecretary',
            'delete ChurchSecretary',
            'restore ChurchSecretary',
            'force-delete ChurchSecretary',
            'view-any ChurchService',
            'view ChurchService',
            'create ChurchService',
            'update ChurchService',
            'delete ChurchService',
            'restore ChurchService',
            'force-delete ChurchService',
            'deactivate ChurchService',
            'view-any ChurchServiceRequest',
            'view ChurchServiceRequest',
            'create ChurchServiceRequest',
            'update ChurchServiceRequest',
            'delete ChurchServiceRequest',
            'restore ChurchServiceRequest',
            'force-delete ChurchServiceRequest',
            'view-any Committee',
            'view Committee',
            'create Committee',
            'update Committee',
            'delete Committee',
            'restore Committee',
            'force-delete Committee',
            'deactivate Committee',
            'view-any Dependant',
            'view Dependant',
            'create Dependant',
            'update Dependant',
            'delete Dependant',
            'restore Dependant',
            'force-delete Dependant',
            'view-any Dinomination',
            'view Dinomination',
            'create Dinomination',
            'update Dinomination',
            'delete Dinomination',
            'restore Dinomination',
            'force-delete Dinomination',
            'view-any Diocese',
            'view Diocese',
            'create Diocese',
            'update Diocese',
            'delete Diocese',
            'restore Diocese',
            'force-delete Diocese',
            'view-any District',
            'view District',
            'create District',
            'update District',
            'delete District',
            'restore District',
            'force-delete District',
            'view-any IntroductionNote',
            'view IntroductionNote',
            'create IntroductionNote',
            'update IntroductionNote',
            'delete IntroductionNote',
            'restore IntroductionNote',
            'force-delete IntroductionNote',
            'approve IntroductionNote',
            'view-any JumuiyaAccountant',
            'view JumuiyaAccountant',
            'create JumuiyaAccountant',
            'update JumuiyaAccountant',
            'delete JumuiyaAccountant',
            'restore JumuiyaAccountant',
            'force-delete JumuiyaAccountant',
            'view-any JumuiyaChairPerson',
            'view JumuiyaChairPerson',
            'create JumuiyaChairPerson',
            'update JumuiyaChairPerson',
            'delete JumuiyaChairPerson',
            'restore JumuiyaChairPerson',
            'view-any JumuiyaMember',
            'view JumuiyaMember',
            'create JumuiyaMember',
            'update JumuiyaMember',
            'delete JumuiyaMember',
            'restore JumuiyaMember',
            'force-delete JumuiyaMember',
            'deactivate JumuiyaMember',
            'view-any Jumuiya',
            'view Jumuiya',
            'create Jumuiya',
            'update Jumuiya',
            'delete Jumuiya',
            'restore Jumuiya',
            'force-delete Jumuiya',
            'view-any JumuiyaRevenue',
            'view JumuiyaRevenue',
            'create JumuiyaRevenue',
            'update JumuiyaRevenue',
            'delete JumuiyaRevenue',
            'restore JumuiyaRevenue',
            'force-delete JumuiyaRevenue',
            'view-any LogOffering',
            'view LogOffering',
            'create LogOffering',
            'update LogOffering',
            'delete LogOffering',
            'restore LogOffering',
            'force-delete LogOffering',
            'view-any Offering',
            'view Offering',
            'create Offering',
            'update Offering',
            'delete Offering',
            'restore Offering',
            'force-delete Offering',
            'view-any Pastor',
            'view Pastor',
            'create Pastor',
            'update Pastor',
            'delete Pastor',
            'restore Pastor',
            'force-delete Pastor',
            'assign Pastor',
            'view-any PastorSchedule',
            'view PastorSchedule',
            'create PastorSchedule',
            'update PastorSchedule',
            'delete PastorSchedule',
            'restore PastorSchedule',
            'force-delete PastorSchedule',
            'view-any PledgePayment',
            'view PledgePayment',
            'create PledgePayment',
            'update PledgePayment',
            'delete PledgePayment',
            'restore PledgePayment',
            'force-delete PledgePayment',
            'view-any Pledge',
            'view Pledge',
            'create Pledge',
            'update Pledge',
            'delete Pledge',
            'restore Pledge',
            'force-delete Pledge',
            'view-any Region',
            'view Region',
            'create Region',
            'update Region',
            'delete Region',
            'restore Region',
            'force-delete Region',
            'view-any Schedule',
            'view Schedule',
            'create Schedule',
            'update Schedule',
            'delete Schedule',
            'restore Schedule',
            'force-delete Schedule',
            'view-any ServiceOffering',
            'view ServiceOffering',
            'create ServiceOffering',
            'update ServiceOffering',
            'delete ServiceOffering',
            'restore ServiceOffering',
            'force-delete ServiceOffering',
            'view-any User',
            'view User',
            'create User',
            'update User',
            'delete User',
            'restore User',
            'force-delete User',
            'view-any Ward',
            'view Ward',
            'create Ward',
            'update Ward',
            'delete Ward',
            'restore Ward',
            'force-delete Ward'
        ];

        $permissions = Arr::map($permissions, function ($permission) {
            return [
                'name' => $permission,
                'guard_name' => 'web',
            ];
        });

        $admin_permissions = Arr::map($admin_permissions, function ($admin_permission) {
            return [
                'name' => $admin_permission,
                'guard_name' => 'admin',
            ];
        });
        Permission::insert($permissions);
        Permission::insert($admin_permissions);

        $dinomination_admin->givePermissionTo([
            'view-any ChurchDistrict',
            'view ChurchDistrict',
            'view-any Dinomination',
            'view Dinomination',
            'create Dinomination',
            'update Dinomination',
            'delete Dinomination',
            'view-any Diocese',
            'view Diocese',
            'create Diocese',
            'update Diocese',
            'delete Diocese',
            'view-any Pastor',
            'view Pastor',
            'create Pastor',
            'update Pastor',
            'delete Pastor',
            'view-any Church',
            'view Church',
            'view-any ChurchSecretary',
            'view ChurchSecretary',
            'create ChurchSecretary',
            'update ChurchSecretary',
            'delete ChurchSecretary',
        ]);

        $diocese_admin->givePermissionTo([
            'view-any ChurchDistrict',
            'view ChurchDistrict',
            'create ChurchDistrict',
            'update ChurchDistrict',
            'delete ChurchDistrict',
            'view-any Pastor',
            'view Pastor',
            'create Pastor',
            'update Pastor',
            'delete Pastor',
            'view-any Church',
            'view Church',
            'create Church',
            'update Church',
            'delete Church',
            'view-any ChurchSecretary',
            'view ChurchSecretary',
            'create ChurchSecretary',
            'update ChurchSecretary',
            'delete ChurchSecretary',
        ]);

        $churchdistrict_admin->givePermissionTo([
            'view-any Church',
            'view Church',
            'create Church',
            'update Church',
            'delete Church',
            'view-any Pastor',
            'view Pastor',
            'create Pastor',
            'update Pastor',
            'delete Pastor',
            'view-any ChurchSecretary',
            'view ChurchSecretary',
            'create ChurchSecretary',
            'update ChurchSecretary',
            'delete ChurchSecretary',
        ]);

        $parish_admin->givePermissionTo([
            'view-any Church',
            'view Church',
            'create Church',
            'update Church',
            'delete Church',
            'view-any Pastor',
            'view Pastor',
            'create Pastor',
            'update Pastor',
            'delete Pastor',
            'view-any ChurchSecretary',
            'view ChurchSecretary',
            'create ChurchSecretary',
            'update ChurchSecretary',
            'delete ChurchSecretary',
        ]);

        $beneficiary->givePermissionTo([
            'view BeneficiaryRequest',
            'verify BeneficiaryRequestPayments'
        ]);

        $committee_member->givePermissionTo([
            'view-any JumuiyaMember',
            'view JumuiyaMember',
            'deactivate JumuiyaMember',
            'create JumuiyaAccountant',
            'create JumuiyaChairPerson',
            'view-any LogOffering',
            'view LogOffering',
            'create LogOffering',
            'update LogOffering',
            'delete LogOffering',
            'view-any Offering',
            'view Offering',
            'create Offering',
            'update Offering',
            'delete Offering',
            'view JumuiyaRevenue',
            'view-any Attendance',
            'view Attendance',
            'create Attendance',
            'update Attendance',
            'delete Attendance'
        ]);

        $jumuiya_chairperson->givePermissionTo([
            'view-any JumuiyaMember',
            'view JumuiyaMember',
            'deactivate JumuiyaMember',
            'view-any Announcement',
            'view JumuiyaRevenue',
        ]);

        $jumuiya_accountant->givePermissionTo([
            'view-any JumuiyaMember',
            'view JumuiyaMember',
            'view JumuiyaRevenue',
            'create JumuiyaRevenue',
            'update JumuiyaRevenue',
            'delete JumuiyaRevenue',
        ]);

        $guest->givePermissionTo([
            'create ChurchMember',
            'view Announcement'
        ]);

        $church_member->givePermissionTo([
            'view BeneficiaryRequest',
            'view Announcement',
            'view IntroductionNote',
            'create IntroductionNote',
            'update IntroductionNote',
            'delete IntroductionNote',
            'view ChurchServiceRequest',
            'create ChurchServiceRequest',
            'update ChurchServiceRequest',
            'delete ChurchServiceRequest',
            'view Beneficiary',
            'view BeneficiaryRequestItemPledge',
            'create BeneficiaryRequestItemPledge',
            'update BeneficiaryRequestItemPledge',
            'delete BeneficiaryRequestItemPledge',
            'view BeneficiaryRequestItemPayment',
            'create BeneficiaryRequestItemPayment',
            'update BeneficiaryRequestItemPayment',
            'delete BeneficiaryRequestItemPayment',
            'view AuctionItemPayment',
            'create AuctionItemPayment',
            'update AuctionItemPayment',
            'delete AuctionItemPayment',
            'view Booking',
            'create Booking',
            'update Booking',
            'delete Booking',
            'view Card',
            'view CardPledge',
            'create CardPledge',
            'update CardPledge',
            'delete CardPledge',
            'update ChurchMember',
            'delete ChurchMember',
            'view Offering',
            'view AuctionItem'
        ]);

        $subparish_pastor->givePermissionTo([
            'view AnnouncementPublications',
            'create AnnouncementPublications',
            'update AnnouncementPublications',
            'delete AnnouncementPublications',
            'view-any Announcement',
            'view Announcement',
            'create Announcement',
            'update Announcement',
            'delete Announcement',
            'view-any Attendance',
            'view Attendance',
            'view-any ChurchMass',
            'view ChurchMass',
            'create ChurchMass',
            'update ChurchMass',
            'delete ChurchMass',
            'view-any ChurchMember',
            'view ChurchMember',
            'update ChurchMember',
            'delete ChurchMember',
            'view-any ChurchService',
            'view ChurchService',
            'create ChurchService',
            'update ChurchService',
            'delete ChurchService',
            'view-any Jumuiya',
            'view Jumuiya',
            'view-any PastorSchedule',
            'view PastorSchedule',
            'create PastorSchedule',
            'update PastorSchedule',
            'delete PastorSchedule',
            'view-any Booking',
            'view-any ChurchMember',
            'view ChurchMember',
            'view-any Attendance',
            'view Attendance',
            'view-any Beneficiary',
            'view Beneficiary',
            'view-any BeneficiaryRequest',
            'view BeneficiaryRequest',
            'view-any ChurchAuction',
            'view ChurchAuction',
            'view-any Card',
            'view Card',
            'view-any ChurchServiceRequest',
            'view ChurchServiceRequest',
            'view-any Committee',
            'view Committee',
            'view-any IntroductionNote',
            'approve IntroductionNote',
            'view-any LogOffering',
            'view LogOffering',
            'view-any Offering',
            'verify ChurchMember'
        ]);

        $pastor->givePermissionTo([
            'view AnnouncementPublications',
            'create AnnouncementPublications',
            'update AnnouncementPublications',
            'delete AnnouncementPublications',
            'view-any Announcement',
            'view Announcement',
            'create Announcement',
            'update Announcement',
            'delete Announcement',
            'view-any Attendance',
            'view Attendance',
            'view-any ChurchMass',
            'view ChurchMass',
            'create ChurchMass',
            'update ChurchMass',
            'delete ChurchMass',
            'view-any ChurchMember',
            'view ChurchMember',
            'update ChurchMember',
            'delete ChurchMember',
            'view-any ChurchService',
            'view ChurchService',
            'create ChurchService',
            'update ChurchService',
            'delete ChurchService',
            'view-any Jumuiya',
            'view Jumuiya',
            'view-any PastorSchedule',
            'view PastorSchedule',
            'create PastorSchedule',
            'update PastorSchedule',
            'delete PastorSchedule',
            'view-any Booking',
            'view-any ChurchMember',
            'view ChurchMember',
            'view-any Attendance',
            'view Attendance',
            'view-any Beneficiary',
            'view Beneficiary',
            'view-any BeneficiaryRequest',
            'view BeneficiaryRequest',
            'view-any ChurchAuction',
            'view ChurchAuction',
            'view-any Card',
            'view Card',
            'view-any ChurchServiceRequest',
            'view ChurchServiceRequest',
            'view-any Committee',
            'view Committee',
            'view-any IntroductionNote',
            'approve IntroductionNote',
            'view-any LogOffering',
            'view LogOffering',
            'view-any Offering',
            'view-any Church',
            'view Church',
            'verify ChurchMember'
        ]);


        $senior_pastor->givePermissionTo([
            'view AnnouncementPublications',
            'create AnnouncementPublications',
            'update AnnouncementPublications',
            'delete AnnouncementPublications',
            'view-any Announcement',
            'view Announcement',
            'create Announcement',
            'update Announcement',
            'delete Announcement',
            'view-any Attendance',
            'view Attendance',
            'view-any ChurchMass',
            'view ChurchMass',
            'create ChurchMass',
            'update ChurchMass',
            'delete ChurchMass',
            'view-any ChurchMember',
            'view ChurchMember',
            'update ChurchMember',
            'delete ChurchMember',
            'view-any ChurchService',
            'view ChurchService',
            'create ChurchService',
            'update ChurchService',
            'delete ChurchService',
            'view-any Jumuiya',
            'view Jumuiya',
            'view-any PastorSchedule',
            'view PastorSchedule',
            'create PastorSchedule',
            'update PastorSchedule',
            'delete PastorSchedule',
            'view-any Booking',
            'view-any ChurchMember',
            'view ChurchMember',
            'view-any Attendance',
            'view Attendance',
            'view-any Beneficiary',
            'view Beneficiary',
            'view-any BeneficiaryRequest',
            'view BeneficiaryRequest',
            'view-any ChurchAuction',
            'view ChurchAuction',
            'view-any Card',
            'view Card',
            'view-any ChurchServiceRequest',
            'view ChurchServiceRequest',
            'view-any Committee',
            'view Committee',
            'view-any IntroductionNote',
            'approve IntroductionNote',
            'view-any LogOffering',
            'view LogOffering',
            'view-any Offering',
            'view-any Church',
            'view Church',
            'verify ChurchMember'
        ]);

        $churchdistrict_pastor->givePermissionTo([
            'view-any Announcement',
            'view Announcement',
            'create Announcement',
            'update Announcement',
            'delete Announcement',
            'view-any Attendance',
            'view Attendance',
            'view-any ChurchMass',
            'view ChurchMass',
            'create ChurchMass',
            'update ChurchMass',
            'delete ChurchMass',
            'view-any ChurchMember',
            'view ChurchMember',
            'update ChurchMember',
            'delete ChurchMember',
            'view-any ChurchService',
            'view ChurchService',
            'create ChurchService',
            'update ChurchService',
            'delete ChurchService',
            'view-any Jumuiya',
            'view Jumuiya',
            'view-any PastorSchedule',
            'view PastorSchedule',
            'create PastorSchedule',
            'update PastorSchedule',
            'delete PastorSchedule',
            'view-any Booking',
            'view-any ChurchMember',
            'view ChurchMember',
            'view-any Attendance',
            'view Attendance',
            'view-any Beneficiary',
            'view Beneficiary',
            'view-any BeneficiaryRequest',
            'view BeneficiaryRequest',
            'view-any ChurchAuction',
            'view ChurchAuction',
            'view-any Card',
            'view Card',
            'view-any ChurchServiceRequest',
            'view ChurchServiceRequest',
            'view-any Committee',
            'view Committee',
            'view-any IntroductionNote',
            'approve IntroductionNote',
            'view-any LogOffering',
            'view LogOffering',
            'view-any Offering',
            'view-any Church',
            'view Church',
            'verify ChurchMember'
        ]);

        $bishop->givePermissionTo([
            'view-any Announcement',
            'view Announcement',
            'create Announcement',
            'update Announcement',
            'delete Announcement',
            'view-any Attendance',
            'view Attendance',
            'view-any ChurchMass',
            'view ChurchMass',
            'create ChurchMass',
            'update ChurchMass',
            'delete ChurchMass',
            'view-any ChurchMember',
            'view ChurchMember',
            'update ChurchMember',
            'delete ChurchMember',
            'view-any ChurchService',
            'view ChurchService',
            'create ChurchService',
            'update ChurchService',
            'delete ChurchService',
            'view-any Jumuiya',
            'view Jumuiya',
            'view-any PastorSchedule',
            'view PastorSchedule',
            'create PastorSchedule',
            'update PastorSchedule',
            'delete PastorSchedule',
            'view-any Booking',
            'view-any ChurchMember',
            'view ChurchMember',
            'view-any Attendance',
            'view Attendance',
            'view-any Beneficiary',
            'view Beneficiary',
            'view-any BeneficiaryRequest',
            'view BeneficiaryRequest',
            'view-any ChurchAuction',
            'view ChurchAuction',
            'view-any Card',
            'view Card',
            'view-any ChurchServiceRequest',
            'view ChurchServiceRequest',
            'view-any Committee',
            'view Committee',
            'view-any IntroductionNote',
            'approve IntroductionNote',
            'view-any LogOffering',
            'view LogOffering',
            'view-any Offering',
            'view-any Church',
            'view Church',
            'verify ChurchMember',
            'view-any ChurchDistrict',
            'view ChurchDistrict',
        ]);

        $archbishop->givePermissionTo([
            'view-any Announcement',
            'view Announcement',
            'create Announcement',
            'update Announcement',
            'delete Announcement',
            'view-any Attendance',
            'view Attendance',
            'view-any ChurchMass',
            'view ChurchMass',
            'create ChurchMass',
            'update ChurchMass',
            'delete ChurchMass',
            'view-any ChurchMember',
            'view ChurchMember',
            'update ChurchMember',
            'delete ChurchMember',
            'view-any ChurchService',
            'view ChurchService',
            'create ChurchService',
            'update ChurchService',
            'delete ChurchService',
            'view-any Jumuiya',
            'view Jumuiya',
            'view-any PastorSchedule',
            'view PastorSchedule',
            'create PastorSchedule',
            'update PastorSchedule',
            'delete PastorSchedule',
            'view-any Booking',
            'view-any ChurchMember',
            'view ChurchMember',
            'view-any Attendance',
            'view Attendance',
            'view-any Beneficiary',
            'view Beneficiary',
            'view-any BeneficiaryRequest',
            'view BeneficiaryRequest',
            'view-any ChurchAuction',
            'view ChurchAuction',
            'view-any Card',
            'view Card',
            'view-any ChurchServiceRequest',
            'view ChurchServiceRequest',
            'view-any Committee',
            'view Committee',
            'view-any IntroductionNote',
            'approve IntroductionNote',
            'view-any LogOffering',
            'view LogOffering',
            'view-any Offering',
            'view-any Church',
            'view Church',
            'verify ChurchMember',
            'view-any ChurchDistrict',
            'view ChurchDistrict',
            'view-any Diocese',
            'view Diocese',
        ]);

        $church_secretary->givePermissionTo([
            'view Announcement',
            'view-any Attendance',
            'view Attendance',
            'view-any ChurchMass',
            'view ChurchMass',
            'create ChurchMass',
            'update ChurchMass',
            'delete ChurchMass',
            'view-any ChurchMember',
            'view ChurchMember',
            'update ChurchMember',
            'delete ChurchMember',
            'view-any ChurchService',
            'view ChurchService',
            'create ChurchService',
            'update ChurchService',
            'delete ChurchService',
            'view-any Jumuiya',
            'view Jumuiya',
            'create Jumuiya',
            'update Jumuiya',
            'delete Jumuiya',
            'view-any PastorSchedule',
            'view PastorSchedule',
            'create PastorSchedule',
            'update PastorSchedule',
            'delete PastorSchedule',
            'view-any Booking',
            'view-any ChurchMember',
            'view ChurchMember',
            'view-any Attendance',
            'view Attendance',
            'view-any Beneficiary',
            'view Beneficiary',
            'view-any BeneficiaryRequest',
            'view BeneficiaryRequest',
            'create BeneficiaryRequest',
            'update BeneficiaryRequest',
            'delete BeneficiaryRequest',
            'view-any ChurchAuction',
            'view ChurchAuction',
            'view-any Card',
            'view Card',
            'create Card',
            'update Card',
            'delete Card',
            'view-any ChurchServiceRequest',
            'view ChurchServiceRequest',
            'view-any Committee',
            'view Committee',
            'create Committee',
            'update Committee',
            'delete Committee',
            'view-any IntroductionNote',
            'approve IntroductionNote',
            'view-any LogOffering',
            'view LogOffering',
            'view-any Offering',
            'create LogOffering',
            'update LogOffering',
            'delete LogOffering',
            'view-any Church',
            'view Church',
            'verify ChurchMember'
        ]);

    }
}
