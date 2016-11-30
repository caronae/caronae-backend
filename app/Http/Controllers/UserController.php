<?php

namespace Caronae\Http\Controllers;

use Caronae\ExcelExport\ExcelExporter;
use Caronae\Http\Requests;
use Caronae\User;
use Caronae\Exception\SigaException;
use Caronae\Services\SigaService;
use Facebook;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function signUpIntranet($idUFRJ, $token, SigaService $siga)
    {
        if (User::where('token', $token)->count() > 0) {
            return response()->json(['error'=>'User token already exists.'], 409);
        }
        if (User::where('id_ufrj', $idUFRJ)->count() > 0) {
            return response()->json(['error'=>'User id_ufrj already exists.'], 409);
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
        $decode = json_decode($request->getContent());
        $matchThese = ['token' => $decode->token, 'id_ufrj' => $decode->id_ufrj];
        $user = User::where($matchThese)->first();
        if ($user == null) {
            return response()->json(['error'=>'User not found with provided credentials.'], 403);
        }

        //get user's rides as driver
        $matchThese = ['status' => 'driver', 'done' => false];
        $drivingRides = $user->rides()->where($matchThese)->get();

        return array("user" => $user, "rides" => $drivingRides);
    }

    public function update(Request $request)
    {
        $decode = json_decode($request->getContent());
        $user = User::where('token', $request->header('token'))->first();
        if (empty($request->header('token')) || $user == NULL) {
            return response()->json(['error'=>'User token not authorized.'], 403);
        }

        $user->profile = $decode->profile;
        $user->course = $decode->course;
        $user->phone_number = $decode->phone_number;
        $user->email = $decode->email;
        $user->location = $decode->location;
        $user->car_owner = $decode->car_owner;
        $user->car_model = $decode->car_model;
        $user->car_color = $decode->car_color;
        $user->car_plate = $decode->car_plate;
        if (@$decode->profile_pic_url != "") {
            $user->profile_pic_url = $decode->profile_pic_url;
        }

        $user->save();
    }

    public function saveGcmToken(Request $request)
    {
        $decode = json_decode($request->getContent());
        $user = User::where('token', $request->header('token'))->first();
        if ($user == null) {
            return response()->json(['error'=>'User token not authorized.'], 403);
        }

        $user->gcm_token = $decode->token;

        $user->save();
    }

    public function saveFaceId(Request $request)
    {
        $decode = json_decode($request->getContent());
        $user = User::where('token', $request->header('token'))->first();
        if ($user == null) {
            return response()->json(['error'=>'User token not authorized.'], 403);
        }

        if ($decode->id) $user->face_id = $decode->id;

        $user->save();
    }

    public function saveProfilePicUrl(Request $request)
    {
        $decode = json_decode($request->getContent());
        $user = User::where('token', $request->header('token'))->first();
        if ($user == null) {
            return response()->json(['error'=>'User token not authorized.'], 403);
        }

        $user->profile_pic_url = $decode->url;

        $user->save();
    }

    public function getMutualFriends(Request $request, $fbid)
    {
        $user = User::where('token', $request->header('token'))->first();
        if ($user == null) {
            return response()->json(['error'=>'User ' . $request->header('token') . ' token not authorized.'], 403);
        }

        $fbtoken = $request->header('Facebook-Token');
        if ($fbtoken == null) {
            return response()->json(['error'=>'User\'s Facebook token missing.'], 403);
        }

        $fb = new Facebook\Facebook([
            'app_id' => '933455893356973',
            'app_secret' => '007b9930ed5a15c407c44768edcbfebd',
            'default_graph_version' => 'v2.5'
        ]);

        try {
            $response = $fb->get('/' . $fbid . '?fields=context.fields(mutual_friends)', $fbtoken);
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            return response()->json(['error'=>'Facebook Graph returned an error: ' . $e->getMessage()], 500);
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            return response()->json(['error'=>'Facebook SDK returned an error: ' . $e->getMessage()], 500);
        }

        $mutualFriendsFB = $response->getGraphObject()['context']['mutual_friends'];
        $totalFriendsCount = $mutualFriendsFB->getMetaData()['summary']['total_count'];

        // Array will hold only the Facebook IDs of the mutual friends
        $friendsFacebookIds = [];
        foreach ($mutualFriendsFB as $friendFB) {
            $friendsFacebookIds[] = $friendFB['id'];
        }

        $mutualFriends = User::whereIn('face_id', $friendsFacebookIds)->get();
        return response()->json(["total_count" => $totalFriendsCount, "mutual_friends" => $mutualFriends]);
    }

    public function loadIntranetPhoto($hash)
    {
        $context = stream_context_create(['http' => ['timeout' => 2]]);
        $intranetResponseRaw = @file_get_contents('http://146.164.2.117:8090/' . $hash, FILE_BINARY, $context);

        // Check if the connection was successful
        if ($intranetResponseRaw == false) {
            return response()->json(['error'=>'Failed to connect to Intranet photos database.'], 500);
        }

        return response($intranetResponseRaw)->header('Content-Type', 'image/jpg');
    }

    public function getIntranetPhotoUrl(Request $request, SigaService $siga)
    {
        $user = User::where('token', $request->header('token'))->first();
        if ($user == null) {
            return response()->json(['error'=>'User ' . $request->header('token') . ' token not authorized.'], 403);
        }

        $idUFRJ = $user->id_ufrj;
        if ($idUFRJ == null || $idUFRJ == '') {
            return response()->json(['error'=>'User does not have an Intranet identification.'], 403);
        }

        $picture = $siga->getProfilePictureById($idUFRJ);
        return response()->json(['url' => $picture]);
    }

    public function index(Request $request)
    {
        return view('users.index')->with('banned', !$request->has('banned'));
    }

    public function indexJson(Request $request)
    {
        if ($request->has('banned')) {
            return User::onlyTrashed()->get();
        } else {
            return User::all();
        }
    }

    public function indexExcel(Request $request)
    {
        $query = User::select('name', 'email', 'profile', 'course', 'location');

        if ($request->has('banned')) {
            $query = $query->onlyTrashed();
        }

        $data = $query->get()->toArray();

        (new ExcelExporter())->export('usuarios', [
            'Nome', 'Email', 'Perfil UFRJ', 'Curso', 'Bairro'
        ], $data, $request->get('type', 'xlsx'));
    }

    public function banish($id)
    {
        $user = User::find($id);

        $user->banish();

        return back()->with('message', 'Usuario "'.$user->name.'" banido com sucesso.');
    }

    public function unban($id)
    {
        $user = User::withTrashed()->find($id);

        $user->unban();

        return back()->with('message', 'Usuario "'.$user->name.'" desbanido com sucesso.');
    }
}
