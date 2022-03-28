<?php
/**
 * File LaravelController.php
 *
 * @author Tuan Duong <bacduong@gmail.com>
 * @package Laravue
 * @version 1.0
 */

namespace App\Http\Controllers;
use Modules\Frontend\Models\League;
use Carbon\Carbon;
use Modules\Frontend\Models\UsersLeague;
use Modules\Frontend\Models\SavedTeam;
use Modules\Sport\Models\LeagueLocation;
use App\User;
use Hash;
use App\Laravue\Models\PermissionGroup;
/**
 * Class LaravueController
 *
 * @package App\Http\Controllers
 */
class LaravueController extends Controller
{
    /**
     * Entry point for Laravue Dashboard
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('laravue');
    }

    public function createleague($user_id,$sport_id,$round_id,$if_forfeit){
		
		$columns = 2000;

    	for ($k = 1 ; $k <= $columns; $k++){
	    	$league = new League();
	    	$league->user_id = $user_id;
	    	$league->league_name = 'Australian Football League-'.$k;
	    	if($k % 2 == 0){ 
	    		$league->type = 'lml';
	    	}
	    	else{
	    		$league->type = 'lms';
	    	}
	    	$league->sport_id = $sport_id;
	    	$league->round_to_start = $round_id;
	    	$league->is_private = 'no';
	    	$league->current_round_id = $round_id;
	    	$league->is_banterboard = 'yes';
	    	$league->if_forfeit = $if_forfeit;
	    	$league->start_datetime = date("Y-m-d h:i:s");
        	$league->end_datetime = date("Y-m-d h:i:s");

	    	$league->save();

	    	$locationData = new LeagueLocation();
            $locationData->league_id = $league->id;
            $locationData->league_town = '';
            $locationData->league_city = 'Acacia Gardens';
            $locationData->league_state = 'NSW';
            $locationData->league_country = 'Australia';
            // print_r($locationData); die;
            if($locationData->save()){

                $leagueUsers = new UsersLeague();
                $leagueUsers->league_id=$league->id;
                $leagueUsers->user_id = $user_id;
                $leagueUsers->sport_id = $sport_id;
                $leagueUsers->is_admin = 'yes';
                $leagueUsers->is_team_pickup = 'no';
                $leagueUsers->is_knockedout = 'no';
                $leagueUsers->is_play = 'no';
                $leagueUsers->status='active';
                $leagueUsers->save();
            }

    	}


    }

    public function joinleagues($sport_id){

    $columns = 10;	
    
    $leagues = League::all();

    $current_users = 0;

    foreach ($leagues as $key => $leaguevalue) {

    		$users = User::take(10)->where('id','>',$current_users)->pluck('id');
    		
    		foreach ($users as $key => $uservalue) {

    			print_r($uservalue.' /'.$leaguevalue->id);
    			echo "<pre>"; 

    			$leagueUsers = new UsersLeague();
                $leagueUsers->league_id=$leaguevalue->id;
                $leagueUsers->user_id = $uservalue;
                $leagueUsers->sport_id = $sport_id;
                $leagueUsers->is_admin = 'no';
                $leagueUsers->is_team_pickup = 'no';
                $leagueUsers->is_knockedout = 'no';
                $leagueUsers->is_play = 'no';
                $leagueUsers->status='active';
                $leagueUsers->save();
    	}
	    	
    	$current_users += 10;

    }

    }


public function createusers(){

	    $passwordStr = '123456789';
        $password = Hash::make($passwordStr);
        // $Getpermission = '2';
        $permission_ids = '2';
        $usercount = 5000;
        $total = 20000;
        $s = 1;
        $users = array();
        while($usercount <= $total) {
          $users = array();
          for ($u=$s; $u <= $usercount; $u++) {
              $data = array();
              $data['first_name'] = 'user '.$u;
              $data['last_name'] = 'jhon';
              $data['email'] = 'league_user'.$u.'@yopmail.com';
              $data['password'] = $password;
              $data['access_permissions'] = $permission_ids;
              $data['role_id'] = 2;
              $data['is_mark_admin'] = 'no';
              $users[] = $data;
          }
          $s = $u;
          $usercount = $usercount+5000;
          User::insert($users);
        }
        echo $total.' user has been created.';
    }


public function teamAllocation($team_1, $team_2, $fixture1, $fixture2) {
	if($team_1!='' && $team_2!='' && $fixture1!='' && $fixture2!='') {
     $userLeague = UsersLeague::all();
     $check = 1;
     foreach ($userLeague as $key => $value) {
     	if($check == 0) {
     		$roundId = League::find($value->league_id)->current_round_id;
     		$new = new SavedTeam;
     		$new->user_id = $value->user_id;
     		$new->league_id = $value->league_id;
     		$new->round_id = $roundId;
     		$new->team_id = $team_2;
     		$new->fixture_id = $fixture2;
     		$new->save();
     	 	$check++;
     	 	echo "UserId-> ".$value->user_id." locked with Team".$team_2."<br>";
     	} else {
     		 $roundId = League::find($value->league_id)->current_round_id;
     		$new = new SavedTeam;
     		$new->user_id = $value->user_id;
     		$new->league_id = $value->league_id;
     		$new->round_id = $roundId;
     		$new->team_id = $team_1;
     		$new->fixture_id = $fixture1;
     		$new->save();
     	 	$check--;
     	 	echo "UserId-> ".$value->user_id." locked with Team ".$team_1."<br>";
     	}

     }
	} else {
		echo "all fields need to fill";
	}
	die();
	


	echo $userCount."<br>";

	echo $team_1."<br>";
	echo $team_2."<br>";
	die();
}

}
