<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\Announcement
 *
 * @property int $id
 * @property int $church_id
 * @property int $user_id
 * @property array $documents
 * @property string $message
 * @property string $date
 * @property int $status
 * @property string $level
 * @property array|null $church
 * @property array|null $jumuiya
 * @property int $jumuiya_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement query()
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement whereChurch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement whereChurchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement whereDocuments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement whereJumuiya($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement whereJumuiyaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Announcement whereUserId($value)
 */
	class Announcement extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Attendance
 *
 * @property int $id
 * @property int $church_id
 * @property int $user_id
 * @property string $date
 * @property string|null $remark
 * @property int $men
 * @property int $women
 * @property int $children
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance query()
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereChildren($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereChurchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereMen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereWomen($value)
 */
	class Attendance extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\BeneficiaryRequest
 *
 * @property int $id
 * @property string $title
 * @property int $church_id
 * @property float $amount
 * @property string $purpose
 * @property array $supporting_documents
 * @property string $date_requested
 * @property string $status_approval
 * @property int $approved_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|BeneficiaryRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BeneficiaryRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BeneficiaryRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|BeneficiaryRequest whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeneficiaryRequest whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeneficiaryRequest whereChurchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeneficiaryRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeneficiaryRequest whereDateRequested($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeneficiaryRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeneficiaryRequest wherePurpose($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeneficiaryRequest whereStatusApproval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeneficiaryRequest whereSupportingDocuments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeneficiaryRequest whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeneficiaryRequest whereUpdatedAt($value)
 */
	class BeneficiaryRequest extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Booking
 *
 * @property int $id
 * @property int $church_member_id
 * @property int $pastor_schedule_id
 * @property string $date_booked
 * @property int $approval_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Booking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Booking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Booking query()
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereApprovalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereChurchMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereDateBooked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking wherePastorScheduleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereUpdatedAt($value)
 */
	class Booking extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Card
 *
 * @property int $id
 * @property string $card_name
 * @property int $church_id
 * @property string $card_description
 * @property int $card_duration
 * @property string $verse_for_card
 * @property string $card_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Church $church
 * @method static \Illuminate\Database\Eloquent\Builder|Card newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Card newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Card query()
 * @method static \Illuminate\Database\Eloquent\Builder|Card whereCardDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Card whereCardDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Card whereCardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Card whereCardStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Card whereChurchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Card whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Card whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Card whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Card whereVerseForCard($value)
 */
	class Card extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ChurchMember
 *
 * @property int $id
 * @property string $first_name
 * @property string $middle_name
 * @property string $surname
 * @property string|null $email
 * @property string $phone
 * @property string|null $date_of_birth
 * @property string|null $date_registered
 * @property string|null $nida_id
 * @property string|null $passport_id
 * @property string|null $picture
 * @property int|null $card_no
 * @property int $region_id
 * @property int $district_id
 * @property int $ward_id
 * @property string|null $street
 * @property string|null $block_no
 * @property string|null $house_no
 * @property string|null $confirmation_place
 * @property string|null $confirmation_date
 * @property string $baptism_place
 * @property string $baptism_date
 * @property string $volunteering_in
 * @property int $sacrament_participation
 * @property string|null $joining_date
 * @property string|null $previous_church
 * @property string $marital_status
 * @property string|null $spouse_name
 * @property string|null $spouse_contact_no
 * @property string|null $education_level
 * @property string|null $profession
 * @property mixed|null $skills
 * @property string|null $work_location
 * @property int $user_id
 * @property int $church_id
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereBaptismDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereBaptismPlace($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereBlockNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereCardNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereChurchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereConfirmationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereConfirmationPlace($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereDateRegistered($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereDistrictId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereEducationLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereHouseNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereJoiningDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereMaritalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereMiddleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereNidaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember wherePassportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember wherePicture($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember wherePreviousChurch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereProfession($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereRegionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereSacramentParticipation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereSkills($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereSpouseContactNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereSpouseName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereStreet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereSurname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereVolunteeringIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereWardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchMember whereWorkLocation($value)
 */
	class ChurchMember extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ChurchSecretary
 *
 * @property int $id
 * @property int $church_member_id
 * @property string $date_registered
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchSecretary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchSecretary newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchSecretary query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchSecretary whereChurchMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchSecretary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchSecretary whereDateRegistered($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchSecretary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchSecretary whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchSecretary whereUpdatedAt($value)
 */
	class ChurchSecretary extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ChurchService
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $date_established
 * @property int $status
 * @property int $church_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchService newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchService newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchService query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchService whereChurchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchService whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchService whereDateEstablished($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchService whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchService whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchService whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchService whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchService whereUpdatedAt($value)
 */
	class ChurchService extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ChurchServiceRequest
 *
 * @property int $id
 * @property int $church_member_id
 * @property int $church_service_id
 * @property string $date_requested
 * @property string $jumuiya_chairperson_comment
 * @property int $approval_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchServiceRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchServiceRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchServiceRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchServiceRequest whereApprovalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchServiceRequest whereChurchMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchServiceRequest whereChurchServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchServiceRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchServiceRequest whereDateRequested($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchServiceRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchServiceRequest whereJumuiyaChairpersonComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChurchServiceRequest whereUpdatedAt($value)
 */
	class ChurchServiceRequest extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Committee
 *
 * @property int $id
 * @property int $church_member_id
 * @property string $date_requested
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Committee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Committee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Committee query()
 * @method static \Illuminate\Database\Eloquent\Builder|Committee whereChurchMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Committee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Committee whereDateRequested($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Committee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Committee whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Committee whereUpdatedAt($value)
 */
	class Committee extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Contribution
 *
 * @property int $id
 * @property string $contribution_type
 * @property string $title
 * @property int $status
 * @property int $church_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Contribution newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Contribution newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Contribution query()
 * @method static \Illuminate\Database\Eloquent\Builder|Contribution whereChurchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contribution whereContributionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contribution whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contribution whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contribution whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contribution whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Contribution whereUpdatedAt($value)
 */
	class Contribution extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Dependant
 *
 * @property int $id
 * @property string $first_name
 * @property string $middle_name
 * @property string $surname
 * @property string $gender
 * @property string $date_of_birth
 * @property string $relationship
 * @property int $church_member_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Dependant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Dependant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Dependant query()
 * @method static \Illuminate\Database\Eloquent\Builder|Dependant whereChurchMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dependant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dependant whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dependant whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dependant whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dependant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dependant whereMiddleName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dependant whereRelationship($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dependant whereSurname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dependant whereUpdatedAt($value)
 */
	class Dependant extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Dinomination
 *
 * @property int $id
 * @property string $name
 * @property int $status
 * @property int $diocese_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Dinomination newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Dinomination newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Dinomination query()
 * @method static \Illuminate\Database\Eloquent\Builder|Dinomination whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dinomination whereDioceseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dinomination whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dinomination whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dinomination whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dinomination whereUpdatedAt($value)
 */
	class Dinomination extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Diocese
 *
 * @property int $id
 * @property string $name
 * @property int $status
 * @property int $region_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Diocese newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Diocese newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Diocese query()
 * @method static \Illuminate\Database\Eloquent\Builder|Diocese whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Diocese whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Diocese whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Diocese whereRegionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Diocese whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Diocese whereUpdatedAt($value)
 */
	class Diocese extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\District
 *
 * @property int $id
 * @property string $name
 * @property int $region_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Region $region
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Ward> $wards
 * @property-read int|null $wards_count
 * @method static \Illuminate\Database\Eloquent\Builder|District newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|District newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|District query()
 * @method static \Illuminate\Database\Eloquent\Builder|District whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|District whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|District whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|District whereRegionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|District whereUpdatedAt($value)
 */
	class District extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\IntroductionNote
 *
 * @property int $id
 * @property int $church_id
 * @property int $church_member_id
 * @property string $title
 * @property string $description
 * @property string $date_requested
 * @property string $date_updated
 * @property int $approval_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|IntroductionNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IntroductionNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|IntroductionNote query()
 * @method static \Illuminate\Database\Eloquent\Builder|IntroductionNote whereApprovalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntroductionNote whereChurchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntroductionNote whereChurchMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntroductionNote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntroductionNote whereDateRequested($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntroductionNote whereDateUpdated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntroductionNote whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntroductionNote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntroductionNote whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|IntroductionNote whereUpdatedAt($value)
 */
	class IntroductionNote extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Jumuiya
 *
 * @property int $id
 * @property string $name
 * @property string $address
 * @property string $phone_number
 * @property int $church_id
 * @property string $date_registered
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Jumuiya newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Jumuiya newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Jumuiya query()
 * @method static \Illuminate\Database\Eloquent\Builder|Jumuiya whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jumuiya whereChurchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jumuiya whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jumuiya whereDateRegistered($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jumuiya whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jumuiya whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jumuiya wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jumuiya whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Jumuiya whereUpdatedAt($value)
 */
	class Jumuiya extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\JumuiyaAccountant
 *
 * @property int $id
 * @property int $church_member_id
 * @property int $jumuiya_id
 * @property string $date_registered
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaAccountant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaAccountant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaAccountant query()
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaAccountant whereChurchMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaAccountant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaAccountant whereDateRegistered($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaAccountant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaAccountant whereJumuiyaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaAccountant whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaAccountant whereUpdatedAt($value)
 */
	class JumuiyaAccountant extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\JumuiyaChairPerson
 *
 * @property int $id
 * @property int $church_member_id
 * @property int $jumuiya_id
 * @property string $date_registered
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaChairPerson newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaChairPerson newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaChairPerson query()
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaChairPerson whereChurchMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaChairPerson whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaChairPerson whereDateRegistered($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaChairPerson whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaChairPerson whereJumuiyaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaChairPerson whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaChairPerson whereUpdatedAt($value)
 */
	class JumuiyaChairPerson extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\JumuiyaMember
 *
 * @property int $id
 * @property int $church_member_id
 * @property int $jumuiya_id
 * @property string $date_registered
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaMember newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaMember newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaMember query()
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaMember whereChurchMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaMember whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaMember whereDateRegistered($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaMember whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaMember whereJumuiyaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaMember whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaMember whereUpdatedAt($value)
 */
	class JumuiyaMember extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\JumuiyaRevenue
 *
 * @property int $id
 * @property int $jumuiya_id
 * @property float $amount
 * @property string $date_recorded
 * @property int $approval_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaRevenue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaRevenue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaRevenue query()
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaRevenue whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaRevenue whereApprovalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaRevenue whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaRevenue whereDateRecorded($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaRevenue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaRevenue whereJumuiyaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|JumuiyaRevenue whereUpdatedAt($value)
 */
	class JumuiyaRevenue extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Offering
 *
 * @property int $id
 * @property int $card_no
 * @property string $card_type
 * @property float $amount_offered
 * @property string $amount_registered_on
 * @property int $church_id
 * @property int $created_by
 * @property int $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Offering newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Offering newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Offering query()
 * @method static \Illuminate\Database\Eloquent\Builder|Offering whereAmountOffered($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offering whereAmountRegisteredOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offering whereCardNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offering whereCardType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offering whereChurchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offering whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offering whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offering whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offering whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Offering whereUpdatedBy($value)
 */
	class Offering extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PastorSchedule
 *
 * @property int $id
 * @property string|null $day
 * @property string $frequency
 * @property int $church_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Schedule> $schedules
 * @property-read int|null $schedules_count
 * @method static \Illuminate\Database\Eloquent\Builder|PastorSchedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PastorSchedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PastorSchedule query()
 * @method static \Illuminate\Database\Eloquent\Builder|PastorSchedule whereChurchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PastorSchedule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PastorSchedule whereDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PastorSchedule whereFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PastorSchedule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PastorSchedule whereUpdatedAt($value)
 */
	class PastorSchedule extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Pledge
 *
 * @property int $id
 * @property float $amount_pledged
 * @property float $amount_completed
 * @property float $amount_remain
 * @property string $date_of_pledge
 * @property string $date_of_contribution
 * @property int $church_id
 * @property int $church_member_id
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Pledge newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pledge newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Pledge query()
 * @method static \Illuminate\Database\Eloquent\Builder|Pledge whereAmountCompleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pledge whereAmountPledged($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pledge whereAmountRemain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pledge whereChurchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pledge whereChurchMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pledge whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pledge whereDateOfContribution($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pledge whereDateOfPledge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pledge whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pledge whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Pledge whereUpdatedAt($value)
 */
	class Pledge extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PledgePayment
 *
 * @property int $id
 * @property int $pledge_id
 * @property float $amount
 * @property string $date_payed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PledgePayment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PledgePayment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PledgePayment query()
 * @method static \Illuminate\Database\Eloquent\Builder|PledgePayment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PledgePayment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PledgePayment whereDatePayed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PledgePayment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PledgePayment wherePledgeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PledgePayment whereUpdatedAt($value)
 */
	class PledgePayment extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Region
 *
 * @property int $id
 * @property string $name
 * @property bool $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\District> $districts
 * @property-read int|null $districts_count
 * @method static \Illuminate\Database\Eloquent\Builder|Region newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Region newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Region query()
 * @method static \Illuminate\Database\Eloquent\Builder|Region whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Region whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Region whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Region whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Region whereUpdatedAt($value)
 */
	class Region extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Role
 *
 * @property int $id
 * @property int $user_id
 * @property string $role
 * @property int $status
 * @property string $priviledge
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role wherePriviledge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereUserId($value)
 */
	class Role extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Schedule
 *
 * @property int $id
 * @property string $from
 * @property string $to
 * @property bool $status
 * @property string|null $day_of_week
 * @property int $pastor_schedule_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PastorSchedule $pastorSchedule
 * @method static \Illuminate\Database\Eloquent\Builder|Schedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Schedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Schedule query()
 * @method static \Illuminate\Database\Eloquent\Builder|Schedule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Schedule whereDayOfWeek($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Schedule whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Schedule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Schedule wherePastorScheduleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Schedule whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Schedule whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Schedule whereUpdatedAt($value)
 */
	class Schedule extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ServiceOffering
 *
 * @property int $id
 * @property int $church_member_id
 * @property int $church_id
 * @property string $date
 * @property float $amount
 * @property int $status
 * @property int $contribution_id
 * @property int $pledge_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOffering newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOffering newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOffering query()
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOffering whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOffering whereChurchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOffering whereChurchMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOffering whereContributionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOffering whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOffering whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOffering whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOffering wherePledgeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOffering whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ServiceOffering whereUpdatedAt($value)
 */
	class ServiceOffering extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @property int $id
 * @property string $phone
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property mixed $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\ChurchMember|null $churchMember
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User withoutTrashed()
 */
	class User extends \Eloquent implements \Filament\Models\Contracts\HasName, \Filament\Models\Contracts\HasAvatar {}
}

namespace App\Models{
/**
 * App\Models\UserRole
 *
 * @property int $id
 * @property int $user_id
 * @property int $role_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserRole newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserRole newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserRole query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserRole whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRole whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRole whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRole whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserRole whereUserId($value)
 */
	class UserRole extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Ward
 *
 * @property int $id
 * @property string $name
 * @property bool $status
 * @property int $district_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\District $district
 * @method static \Illuminate\Database\Eloquent\Builder|Ward newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ward newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ward query()
 * @method static \Illuminate\Database\Eloquent\Builder|Ward whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ward whereDistrictId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ward whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ward whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ward whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ward whereUpdatedAt($value)
 */
	class Ward extends \Eloquent {}
}

