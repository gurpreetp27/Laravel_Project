<?php

namespace Modules\Sport\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use \App\Laravue\JsonResponse;
use Image;
use Modules\Sport\Models\Round;
use Modules\Sport\Models\Sport;
use Auth;
use File;
use App\Repositories\SportRepository;
 
class RoundController extends Controller
{
    protected $model;

    public function __construct(Round $round, Sport $sport)
   {
       // set the model
       $this->model = new SportRepository($round);
       $this->sport = new SportRepository($sport);
   }
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index(Request $request)
    {
        $rowsNumber = 1;   
        $data = [];

        if($request->string!='') {
        
         $rounds = $this->model->paginateWithSearch($request->limit, $request->page,'sport',$request->string);  
        } else {

         $rounds = $this->model->paginateWith($request->limit, $request->page,'sport'); 
        }

        $sport = $this->sport->getUniquSport();
        return response()->json(new JsonResponse(['items' => $rounds,'sport'=>$sport]));
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
        $allData = $request->all();
        for ($i=1; $i <= $request->round_number; $i++) {
                $data = array();
                $data['sport_id'] = $request->sport_id;
                $data['round_number'] = $i;
                $data['round_name'] = $request->round_name[$i];
                $data['round_description'] = $request->round_description[$i];
                $data['added_by'] = Auth::user()->id;
                $data['start_datetime'] = $request->start_datetime[$i];
                // $data['start_datetime'] = date('Y-m-d H:i:s',strtotime('+5 hour +30 minutes',strtotime($request->start_datetime[$i])));
                // $data['end_datetime'] = date('Y-m-d H:i:s',strtotime('+5 hour +30 minutes',strtotime($request->end_datetime[$i])));
                $data['end_datetime'] = $request->end_datetime[$i];
                $this->model->create($data);
            
        }
        
        
        $response = array(
            'status' => 'success',
        );
        return response()->json(new JsonResponse(['items' => 1, 'total' => 2]));
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
        
        // $round_detail = Round::where('id',$id)->first();

        // if($round_detail->start_datetime == $request->start_datetime){
        //     $data['start_datetime'] = $round_detail->start_datetime;
        // }
        // else{

        //     $data['start_datetime'] = date('Y-m-d H:i:s',strtotime('+5 hour +30 minutes',strtotime($request->start_datetime)));
        // }

        // if($round_detail->end_datetime == $request->end_datetime){
        //   $data['end_datetime'] = $round_detail->end_datetime;
        // }
        // else{

        //     $data['end_datetime'] = date('Y-m-d H:i:s',strtotime('+5 hour +30 minutes',strtotime($request->end_datetime)));
        // }

        $data['start_datetime'] = $request->start_datetime;
        $data['end_datetime'] = $request->end_datetime;

        $this->model->update($id,$data);
        $response = array(
            'status' => 'success',
        );
        return response()->json(new JsonResponse(['items' => $data]));
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

    /* Get Rounds according to specific sport */

    public function fetchroundsbysport($sport_id)
    {
        //die("here");
        $rounds = $this->model->wheredate($sport_id)->get();
        return response()->json(new JsonResponse($rounds));
    }

    public function fetchroundsbysportleague($sport_id)
    {
        //die("here");
        $rounds = $this->model->wheredateleague($sport_id)->get();
        return response()->json(new JsonResponse($rounds));
    }

    // Get sports without pagination
    public function roundsforfixture(){

        $fixture = $this->model->all();
        return response()->json(new JsonResponse(['items' => $fixture, 'total' => 2]));
    }
}
