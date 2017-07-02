<?php

namespace Caronae\Http\Controllers;

use Carbon\Carbon;
use Caronae\Http\Requests\SignUpRequest;
use Caronae\Models\User;
use Caronae\Services\SigaService;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.v1.auth', ['only' => [
            'getOfferedRides',
            'update',
            'saveFaceId',
            'saveProfilePicUrl',
            'getMutualFriends',
            'getIntranetPhotoUrl'
        ]]);

        $this->middleware('api.v1.userMatchesRequestedUser', ['only' => ['getOfferedRides']]);
        $this->middleware('api.institution', ['only' => ['store']]);
    }

    public function store(SignUpRequest $request)
    {
        if (!$user = User::where('id_ufrj', $request->id_ufrj)->first()) {
            $user = new User;
            $user->generateToken();
        }

        $user->fill($request->all());
        $user->save();

        $token = JWTAuth::fromUser($user);
        return [ 'user' => $user->fresh(), 'token' => $token ];
    }

    public function signUpIntranet($idUFRJ, $token, SigaService $siga)
    {
        if (User::where('token', $token)->count() > 0) {
            return $this->error('User token already exists.', 409);
        }
        if (User::where('id_ufrj', $idUFRJ)->count() > 0) {
            return $this->error('User id_ufrj already exists.', 409);
        }

        $intranetUser = $siga->getProfileById($idUFRJ);
        $user = new User();
        $user->name = mb_convert_case($intranetUser->nome, MB_CASE_TITLE, "UTF-8");
        $user->token = $token;
        $user->id_ufrj = $idUFRJ;
        $user->course = $intranetUser->nomeCurso;
        if ($intranetUser->alunoServidor == "1") {
            $user->profile = "Servidor";
        } else {
            $user->profile = $intranetUser->nivel;
        }

        if (!empty($intranetUser->urlFoto)) {
            $user->profile_pic_url = $intranetUser->urlFoto;
        }

        $user->save();
        return $user;
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

        return ['user' => $user, 'rides' => $drivingRides];
    }

    public function getOfferedRides(User $user, Request $request)
    {
        $rides = $user->rides()
            ->where('date', '>=', Carbon::now())
            ->where(['done' => false, 'status' => 'driver'])
            ->get();

        $rides = $rides->map(function ($ride) {
            $ride->riders = $ride->riders();
            return $ride;
        });

        return ['rides' => $rides];
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

    public function saveFaceId(Request $request)
    {
        $this->validate($request, [
            'id' => 'required'
        ]);

        $request->currentUser->face_id = $request->id;
        $request->currentUser->save();
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
        return ['total_count' => $totalFriendsCount, 'mutual_friends' => $mutualFriends];
    }

    public function getIntranetPhotoUrl(Request $request, SigaService $siga)
    {
        $idUFRJ = $request->currentUser->id_ufrj;
        if (empty($idUFRJ)) {
            return $this->error('User does not have an Intranet identification.', 404);
        }

        $picture = $siga->getProfilePictureById($idUFRJ);
        return ['url' => $picture];
    }
}
