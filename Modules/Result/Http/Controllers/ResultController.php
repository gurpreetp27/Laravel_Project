<?php

namespace Modules\Result\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use \App\Laravue\JsonResponse;
use Modules\Sport\Models\Round;
use Modules\Sport\Models\Fixture;
use Modules\Sport\Models\League;
use Modules\Sport\Models\Sport;
use Modules\Frontend\Models\UsersLeague;
use Modules\Frontend\Models\SavedTeam;
use App\Repositories\ResultRepository;
use Modules\Result\Models\userStatsUndu;
use App\Helper\ResultNotification;


class ResultController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    protected $model;  

    public function __construct(Round $Round, Fixture $fixture, League $league, UsersLeague $userleague, SavedTeam $userteam, userStatsUndu $undu, Sport $sp)
   {
       // set the model
       $this->model = new ResultRepository($Round);
       $this->fix = new ResultRepository($fixture);
       $this->getLeague = new ResultRepository($league); 
       $this->userOfLeague = new ResultRepository($userleague);
       $this->userTeam = new ResultRepository($userteam);
       $this->unduStats = new ResultRepository($undu);
       $this->sport = new ResultRepository($sp);


   } 
    public function index(Request $request)
    {  
	        $rowsNumber = 1;   
	        $data = [];
	        if($request->string!='') {
	         $rounds = $this->model->paginateWithSearch($request->limit, $request->page,'sport',$request->string);	
	        } else {
	         $rounds = $this->model->paginateWith($request->limit, $request->page,'sport');	
	        }
            $sport = $this->sport->getUniquRound();
             return response()->json(new JsonResponse(['items' => $rounds,'sport' => $sport]));
    	     //$data = $this->model->getallRounds();
    	     //return response()->json(new JsonResponse(['items' => $data]));
    }
    public function getFixture($id, $s_id) {
     $data = $this->fix->getFixture($id, $s_id);
     $undoId  = null;
     $isRoundUnduable = 'no';
     $undoId = $this->unduStats->getUndoRecord($id);
     $isRoundUnduable = $this->model->checkRoundUnduable($id);
     return response()->json(new JsonResponse(['items' => $data, 'undoStats' => $undoId,'roundStatus' => $isRoundUnduable]));
    }
    public function setAbondon($id) {
    	echo $id;
    	die();
    	$data = $this->fix->settoAbondon($id);
    	return response()->json(new JsonResponse());
    }
    public function saveResult( Request $request ) {		
    	$sportID = $this->model->getSportId($request->round_id);
    	$league = $this->getLeague->getLeagueList($sportID, $request->round_id);
    	if(!empty($league)) {
    		$leagueUser =  $this->userOfLeague->getLeagueUser($league);
    		$abondon = $this->fix->abondonHandler($request->abondonList, $leagueUser, $request->round_id); 
            //$abondon = 'false'; 
    		$filtered_array = array_filter($request->fixture);
    		if($abondon=='false') {
    		  if(count($filtered_array) > 0) {
                $this->fix->saveResult($filtered_array);  
    		  }	else {
    		  	return response()->json(new JsonResponse(['status' => 'error', 'message' => 'Something went wrong!']));
    		  }
    		} 
    		
            $string = $this->userOfLeague->getPreRoundStats($leagueUser);
    		//$getWinner = $this->fix->filterWinner($request->status, $request->largestKey);
    		if( $this->userTeam->markWinner($leagueUser, $request->round_id, $sportID) ) {
               if( $this->unduStats->addUnduRecord( $leagueUser, $filtered_array, $request->round_id, $sportID, $string) ) {
                 //ResultNotification::resultNotification($request->round_id);
               	 return response()->json(new JsonResponse(['status' => 'success']));
               }
    		}  	  	
    	} else {
    		return response()->json(new JsonResponse(['status' => 'error', 'message' => 'No Active League Found!']));
    	}
    }

    public function undoResult ( Request $request ) {
    	if( $request->id ) {
    		if($this->unduStats->undoResultUpdate( $request->id )) {
    		 return response()->json(new JsonResponse(['status' => 'success']));	
    		} else {
    			return response()->json(new JsonResponse(['status' => 'error']));
    		}
    	}
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('result::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('result::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('result::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
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
}
