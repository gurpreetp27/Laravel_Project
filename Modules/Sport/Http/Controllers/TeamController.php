<?php

namespace Modules\Sport\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use \App\Laravue\JsonResponse;
use Image;
use Modules\Sport\Models\Team;
use Auth;
use File;
use App\Repositories\SportRepository;
use Illuminate\Support\Facades\Validator;

class TeamController extends Controller
{
    protected $model;
 
    public function __construct(Team $team)
   {
       // set the model
       $this->model = new SportRepository($team);
   }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
        $rowsNumber = 1;   
        $data = [];
        $teams = $this->model->paginateWith($request->limit, $request->page,'sport');
        return response()->json(new JsonResponse(['items' => $teams]));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('sport::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($request->all(), [
        'team_name' => 'required',
        'sport_id' => 'required',
        'team_icon' => 'required|base64image:jpeg,jpg,png'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }
        else {
            $path1 = public_path('uploads');
            if (!File::exists($path1)) {
              File::makeDirectory($path1, 0777, true);
            }

            $path2 = public_path('uploads/team');
            if (!File::exists($path2)) {
              File::makeDirectory($path2, 0777, true);
            }
            $team_icon = "icon-".time().".png";
            $path = public_path().'/uploads/team/' . $team_icon;
            Image::make(file_get_contents($request->team_icon))->save($path);   

            $data['team_icon'] = $team_icon;
            $data['added_by'] = Auth::user()->id;
          
            $this->model->create($data);
            return response()->json(new JsonResponse(['status' => 'success']));
        }    

    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
     public function show($id)
    {
        $sports = $this->model->get($id);
        return response()->json(new JsonResponse($sports));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('sport::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();
        $valid = [];
        $valid['team_name'] = 'required';
        $valid['sport_id'] = 'required';

        if (preg_match('/^data:/', $request->team_icon)) {
            $valid['team_icon'] = 'required|base64image:jpeg,jpg,png';
        } 

        $validator = Validator::make($request->all(), $valid);
        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()->all()]);
        } else {
            if (preg_match('/^data:image\/(\w+);base64,/', $request->team_icon)) {
                $team_icon = "icon-".time().".png";
                $path = public_path().'/uploads/team/' . $team_icon;
                Image::make(file_get_contents($request->team_icon))->save($path);   
                $data['team_icon'] = $team_icon;
            }
            $data['added_by'] = Auth::user()->id;
            $this->model->update($id,$data);
            $response = array(
                'status' => 'success',
            );
            return response()->json(new JsonResponse(['status' => 'success']));
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    // Get sports without pagination
    public function teamsforfixture($id){
        // die("here");
        $teams = $this->model->where($id)->get();

        return response()->json(new JsonResponse(['items' => $teams, 'total' => 2]));
    }
}
