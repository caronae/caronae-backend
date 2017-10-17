<?php
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace Caronae\Models{
/**
 * Caronae\Models\Admin
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $remember_token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Admin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Admin whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Admin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Admin whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Admin wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Admin whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Admin whereUpdatedAt($value)
 */
	class Admin extends \Eloquent {}
}

namespace Caronae\Models{
/**
 * Caronae\Models\Campus
 *
 * @property int $id
 * @property int $institution_id
 * @property string $name
 * @property string|null $color
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read mixed $full_name
 * @property-read \Illuminate\Database\Eloquent\Collection|\Caronae\Models\Hub[] $hubs
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Campus whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Campus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Campus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Campus whereInstitutionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Campus whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Campus whereUpdatedAt($value)
 */
	class Campus extends \Eloquent {}
}

namespace Caronae\Models{
/**
 * Caronae\Models\Hub
 *
 * @property int $id
 * @property string $name
 * @property string $center
 * @property int $campus_id
 * @property-read \Caronae\Models\Campus $campus
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Hub whereCampusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Hub whereCenter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Hub whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Hub whereName($value)
 */
	class Hub extends \Eloquent {}
}

namespace Caronae\Models{
/**
 * Caronae\Models\Institution
 *
 * @property int $id
 * @property string $name
 * @property string $password
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $authentication_url
 * @property-read \Illuminate\Database\Eloquent\Collection|\Caronae\Models\Campus[] $campi
 * @property-read \Illuminate\Database\Eloquent\Collection|\Caronae\Models\User[] $users
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Institution whereAuthenticationUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Institution whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Institution whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Institution whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Institution wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Institution whereUpdatedAt($value)
 */
	class Institution extends \Eloquent {}
}

namespace Caronae\Models{
/**
 * Caronae\Models\Message
 *
 * @property int $id
 * @property int $ride_id
 * @property int $user_id
 * @property string $body
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property-read mixed $date
 * @property-read \Caronae\Models\Ride $ride
 * @property-read \Caronae\Models\User $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\Caronae\Models\Message onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Message whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Message whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Message whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Message whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Message whereRideId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Message whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Message whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\Caronae\Models\Message withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\Caronae\Models\Message withoutTrashed()
 */
	class Message extends \Eloquent {}
}

namespace Caronae\Models{
/**
 * Caronae\Models\Neighborhood
 *
 * @property int $id
 * @property string $name
 * @property float $distance
 * @property int $zone_id
 * @property-read \Caronae\Models\Zone $zone
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Neighborhood whereDistance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Neighborhood whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Neighborhood whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Neighborhood whereZoneId($value)
 */
	class Neighborhood extends \Eloquent {}
}

namespace Caronae\Models{
/**
 * Caronae\Models\Ride
 *
 * @property int $id
 * @property string $myzone
 * @property string $neighborhood
 * @property bool $going
 * @property string|null $place
 * @property string|null $route
 * @property int|null $routine_id
 * @property string|null $hub
 * @property int $slots
 * @property string|null $description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string|null $week_days
 * @property string|null $repeats_until
 * @property bool $done
 * @property \Carbon\Carbon|null $deleted_at
 * @property \Carbon\Carbon $date
 * @property-read mixed $available_slots
 * @property-read mixed $my_date
 * @property-read mixed $my_time
 * @property-read mixed $title
 * @property-read \Illuminate\Database\Eloquent\Collection|\Caronae\Models\Message[] $messages
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\Caronae\Models\User[] $users
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Ride finished()
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Ride inTheFuture()
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Ride notFinished()
 * @method static \Illuminate\Database\Query\Builder|\Caronae\Models\Ride onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Ride whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Ride whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Ride whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Ride whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Ride whereDone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Ride whereGoing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Ride whereHub($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Ride whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Ride whereMyzone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Ride whereNeighborhood($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Ride wherePlace($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Ride whereRepeatsUntil($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Ride whereRoute($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Ride whereRoutineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Ride whereSlots($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Ride whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Ride whereWeekDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Ride withAvailableSlots()
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Ride withFilters($filters = array())
 * @method static \Illuminate\Database\Query\Builder|\Caronae\Models\Ride withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\Caronae\Models\Ride withoutTrashed()
 */
	class Ride extends \Eloquent {}
}

namespace Caronae\Models{
/**
 * Caronae\Models\RideUser
 *
 * @property int $id
 * @property int $user_id
 * @property int $ride_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $status
 * @property string|null $feedback
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\RideUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\RideUser whereFeedback($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\RideUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\RideUser whereRideId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\RideUser whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\RideUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\RideUser whereUserId($value)
 */
	class RideUser extends \Eloquent {}
}

namespace Caronae\Models{
/**
 * Caronae\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string|null $profile
 * @property string|null $course
 * @property string|null $phone_number
 * @property string|null $email
 * @property bool $car_owner
 * @property string|null $car_model
 * @property string|null $car_color
 * @property string|null $car_plate
 * @property string|null $token
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string|null $location
 * @property string|null $face_id
 * @property string|null $profile_pic_url
 * @property \Carbon\Carbon|null $deleted_at
 * @property string|null $id_ufrj
 * @property string|null $app_platform
 * @property string|null $app_version
 * @property bool $banned
 * @property int $institution_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\Caronae\Models\Ride[] $activeRides
 * @property-read \Caronae\Models\Institution $institution
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\Caronae\Models\Ride[] $offeredRides
 * @property-read \Illuminate\Database\Eloquent\Collection|\Caronae\Models\Ride[] $pendingRides
 * @property-read \Illuminate\Database\Eloquent\Collection|\Caronae\Models\Ride[] $rides
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\Caronae\Models\User onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\User whereAppPlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\User whereAppVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\User whereBanned($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\User whereCarColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\User whereCarModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\User whereCarOwner($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\User whereCarPlate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\User whereCourse($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\User whereFaceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\User whereIdUfrj($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\User whereInstitutionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\User whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\User wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\User whereProfile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\User whereProfilePicUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\User whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\Caronae\Models\User withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\Caronae\Models\User withoutTrashed()
 */
	class User extends \Eloquent {}
}

namespace Caronae\Models{
/**
 * Caronae\Models\Zone
 *
 * @property int $id
 * @property string $name
 * @property string|null $color
 * @property-read \Illuminate\Database\Eloquent\Collection|\Caronae\Models\Neighborhood[] $neighborhoods
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Zone whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Zone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Caronae\Models\Zone whereName($value)
 */
	class Zone extends \Eloquent {}
}

