<?php

namespace Caronae\Http\Controllers;

use Caronae\ExcelExport\ExcelExporter;
use Caronae\Http\Requests;
use Caronae\Models\User;
use Caronae\Exception\SigaException;
use Caronae\Services\SigaService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('api.v1.auth', ['only' => [
            'getOfferedRides',
            'update',
            'saveGcmToken',
            'saveFaceId',
            'saveProfilePicUrl',
            'getMutualFriends',
            'getIntranetPhotoUrl'
        ]]);

        $this->middleware('api.v1.userMatchesRequestedUser', ['only' => [
            'getOfferedRides'
        ]]);
    }

    public function signUpIntranet($idUFRJ, $token, SigaService $siga)
    {
        if (User::where('token', $token)->count() > 0) {
            return response()->json(['error' => 'User token already exists.'], 409);
        }
        if (User::where('id_ufrj', $idUFRJ)->count() > 0) {
            return response()->json(['error' => 'User id_ufrj already exists.'], 409);
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

        if (isset($intranetUser->urlFoto) && $intranetUser->urlFoto != '') {
            list($photoHost, $photoHash) = explode('/', $intranetUser->urlFoto);
            if ($photoHost == '146.164.2.117:8090') {
                $user->profile_pic_url = 'https://api.caronae.ufrj.br/user/intranetPhoto/' . $photoHash;
            }
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
        if ($user == null) {
            return response()->json(['error' => 'User not found with provided credentials.'], 403);
        }

        // get user's rides as driver
        $drivingRides = $user->rides()->where(['status' => 'driver', 'done' => false])->get();

        return ['user' => $user, 'rides' => $drivingRides];
    }

    public function getOfferedRides(User $user, Request $request)
    {
        $rides = $user->rides()->where('date', '>', 'NOW()')->where(['done' => false, 'status' => 'driver'])->get();
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

    public function saveGcmToken(Request $request)
    {
        // TODO: Deprecate
        $request->currentUser->gcm_token = $request->token;
        $request->currentUser->save();
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

    public function getMutualFriends(Request $request, \Facebook\Facebook $fb, $fbID)
    {
        $fbToken = $request->header('Facebook-Token');
        if ($fbToken == null) {
            return response()->json(['error' => 'User\'s Facebook token missing.'], 403);
        }

        try {
            $response = $fb->get('/' . $fbID . '?fields=context.fields(mutual_friends)', $fbToken);
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            return response()->json(['error' => 'Facebook Graph returned an error: ' . $e->getMessage()], 500);
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            return response()->json(['error' => 'Facebook SDK returned an error: ' . $e->getMessage()], 500);
        }

        $mutualFriendsFB = $response->getGraphObject()['context']['mutual_friends'];
        $totalFriendsCount = $mutualFriendsFB->getMetaData()['summary']['total_count'];

        // Array will hold only the Facebook IDs of the mutual friends
        $friendsFacebookIds = [];
        foreach ($mutualFriendsFB as $friendFB) {
            $friendsFacebookIds[] = $friendFB['id'];
        }

        $mutualFriends = User::whereIn('face_id', $friendsFacebookIds)->get();
        return response()->json(['total_count' => $totalFriendsCount, 'mutual_friends' => $mutualFriends]);
    }

    public function loadIntranetPhoto($hash)
    {
        $context = stream_context_create(['http' => ['timeout' => 2]]);
        $intranetResponseRaw = @file_get_contents('http://146.164.2.117:8090/' . $hash, FILE_BINARY, $context);

        // Check if the connection was successful
        if ($intranetResponseRaw == false) {
            return response()->json(['error' => 'Failed to connect to Intranet photos database.'], 500);
        }

        return response($intranetResponseRaw)->header('Content-Type', 'image/jpg');
    }

    public function getIntranetPhotoUrl(Request $request, SigaService $siga)
    {
        $idUFRJ = $request->currentUser->id_ufrj;
        if ($idUFRJ == null || $idUFRJ == '') {
            return response()->json(['error' => 'User does not have an Intranet identification.'], 403);
        }

        $picture = $siga->getProfilePictureById($idUFRJ);
        return response()->json(['url' => $picture]);
    }
}
