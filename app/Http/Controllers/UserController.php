<?php

namespace Caronae\Http\Controllers;

use Caronae\Http\Requests\SignUpRequest;
use Caronae\Http\Resources\RideResource;
use Caronae\Http\Resources\UserResource;
use Caronae\Models\User;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Illuminate\Http\Request;
use Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.v1.auth', ['only' => [
            'getOfferedRides',
            'getPendingRides',
            'update',
            'saveFacebookId',
            'saveProfilePicUrl',
            'getMutualFriends',
            'getIntranetPhotoUrl',
        ]]);

        $this->middleware('api.v1.userMatchesRequestedUser', ['only' => [
            'getOfferedRides',
            'getPendingRides',
        ]]);

        $this->middleware('api.institution', ['only' => ['store']]);
    }

    public function store(SignUpRequest $request)
    {
        $institutionID = $request->input('id_ufrj');
        if (!$user = User::findByInstitutionId($institutionID)) {
            $user = new User;
            $user->generateToken();
            $user->institution()->associate($request->institution);

            Log::info("Novo cadastro (id institucional: $institutionID)");
        }

        $user->fill($request->except('token'));
        $user->save();

        $token = JWTAuth::fromUser($user);
        return [ 'user' => new UserResource($user), 'token' => $token ];
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'id_ufrj' => 'required',
            'token' => 'required'
        ]);

        $user = User::where(['id_ufrj' => $request->id_ufrj, 'token' => $request->token])->first();
        if ($user == null || $user->banned) {
            return $this->error('User not found with provided credentials.', 401);
        }

        // get user's rides as driver
        $drivingRides = $user->rides()->where(['status' => 'driver', 'done' => false])->get();

        return ['user' => new UserResource($user), 'rides' => $drivingRides];
    }

    public function getRides(User $user)
    {
        $pendingRides = $user->pendingRides()->with('riders')->get();
        $activeRides = $user->activeRides()->with('riders')->get();
        $offeredRides = $user->availableRides()->with('riders')->get()->diff($activeRides);

        return [
            'pending_rides' => RideResource::collection($pendingRides),
            'active_rides' => RideResource::collection($activeRides),
            'offered_rides' => RideResource::collection($offeredRides),
        ];
    }

    public function getOfferedRides(User $user)
    {
        $rides = $user->offeredRides()
            ->inTheFuture()
            ->notFinished()
            ->with('riders')
            ->get();

        return ['rides' => RideResource::collection($rides)];
    }

    public function getPendingRides(User $user)
    {
        $rides = $user->pendingRides()->get();

        return ['rides' => RideResource::collection($rides)];
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'phone_number' => 'numeric|max:999999999999',
            'email' => 'email',
            'location' => 'string',
            'car_owner' => 'boolean',
            'car_model' => 'required_if:car_owner,true,1|string|max:25',
            'car_color' => 'required_if:car_owner,true,1|string|max:25',
            'car_plate' => 'required_if:car_owner,true,1|regex:/[a-zA-Z]{3}-?[0-9]{4}$/',
            'profile_pic_url' => 'url'
        ]);

        $user = $request->currentUser;

        $user->phone_number = $request->phone_number;
        $user->email = strtolower($request->email);
        $user->location = $request->location;

        $user->car_owner = $request->car_owner;
        if ($request->car_owner) {
            $user->car_model = $request->car_model;
            $user->car_color = $request->car_color;
            $user->car_plate = strtoupper($request->car_plate);
        }

        if (isset($request->profile_pic_url)) {
            $user->profile_pic_url = $request->profile_pic_url;
        }

        $user->save();
    }

    public function saveFacebookId(Request $request)
    {
        $this->validate($request, [
            'id' => 'required'
        ]);

        $user = $request->currentUser;
        $user->face_id = $request->input('id');
        $user->save();
    }

    public function saveProfilePicUrl(Request $request)
    {
        $this->validate($request, [
            'url' => 'required'
        ]);

        $request->currentUser->profile_pic_url = $request->url;
        $request->currentUser->save();
    }

    public function getMutualFriends(Request $request, Facebook $fb, $fbID)
    {
        $fbToken = $request->header('Facebook-Token');
        if ($fbToken == null) {
            return $this->error('User\'s Facebook token missing.', 403);
        }

        try {
            $response = $fb->get('/' . $fbID . '?fields=context.fields(mutual_friends)', $fbToken);
        } catch(FacebookSDKException $e) {
            return $this->error('Facebook SDK returned an error: ' . $e->getMessage(), 500);
        }

        $mutualFriendsFB = $response->getGraphNode()['context']['mutual_friends'];
        $totalFriendsCount = $mutualFriendsFB->getMetaData()['summary']['total_count'];
        $mutualFriendsFB = collect($mutualFriendsFB)->pluck('id');

        $mutualFriends = User::whereIn('face_id', $mutualFriendsFB)->get();
        return ['total_count' => $totalFriendsCount, 'mutual_friends' => UserResource::collection($mutualFriends)];
    }
}
