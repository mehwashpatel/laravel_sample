<?php

namespace App\Repositories;

use App\Agency;
use App\Agent;
use App\User;
use App\Repositories\InfluencerRepository;

class AgencyRepository {
    
    public function create($data) {
        $agency = new Agency();
        
        //create a unique agency number
        $unique = false;
        $number = "";
        while($unique == false) {
            $number = "Y".(mt_rand(10000000,99999999));
            $result = Agency::where('agency_number','=',$number)->get();
            if($result->count() == 0) {
                $unique = true;
            }
        }
        $agency->agency_number = $number;
        if($agency->save()) {
            $agency = $this->update($agency,$data);
            return $agency;
        }
        
        return false;
    }
    
    public function update(Agency $agency, array$data) {
        if(isset($data['company_name'])) {
            $agency->company_name = $data['company_name'];
        }
        if(isset($data['phone_number'])) {
            $agency->phone_number = $data['phone_number'];
        }
        if(isset($data['partnership_goals'])) {
            $agency->partnership_goals = $data['partnership_goals'];
        }
        if(isset($data['agency_number'])) {
            $agency->agency_number = $data['agency_number'];
        }
        if(isset($data['main_agent_id'])) {
            $agency->main_agent_id = $data['main_agent_id'];
        }
        if(isset($data['verified'])) {
            $agency->verified = $data['verified'];
        }
        if(isset($data['approved'])) {
            $agency->approved = $data['approved'];
        }
        if(isset($data['address'])) {
            $agency->address = $data['address'];
        }
        if(isset($data['city'])) {
            $agency->city = $data['city'];
        }
        if(isset($data['province'])) {
            $agency->province = $data['province'];
        }
        if(isset($data['postal_code'])) {
            $agency->postal_code = $data['postal_code'];
        }
        if(isset($data['country'])) {
            $agency->country = $data['country'];
        }
        if(isset($data['special_remarks'])) {
            $agency->special_remarks = $data['special_remarks'];
        }
        
        if($agency->save()) {
            return $agency;
        } else {
            return false;
        }
    }
    
    public function deleteAgency($agencyid) {
        echo "agency id:".$agencyid."<br/>";
        $infrepo = new InfluencerRepository();
        $agency = Agency::find($agencyid);
        dump($agency);
        foreach($agency->agents as $agent) {
            $agtUser = $agent->user;// MainUser::where('user_type_id', '=', $agent->id)->where('usertype','=',config('roles.agent_slug'))->first();
            foreach($agent->influencers as $inf) {
                $infrepo->deleteInfluencer($inf->id);
            }
            $agent->delete();
            
            if($agtUser){
                $agtUser->delete();
            }
            
               
        }

        $agency->delete();
    }
    
    public function retrieveByNumber($agencynumber) {
        return Agency::where('agency_number','=',$agencynumber)->first();
    }
    
   /**
	 * Pull a particular property from each assoc. array in a numeric array, 
	 * returning and array of the property values from each item.
	 *
	 *  @param  array  $a    Array to get data from
	 *  @param  string $prop Property to read
	 *  @return array        Array of property values
	 */
	static function pluck ( $a, $prop )
	{
		$out = array();

		for ( $i=0, $len=count($a) ; $i<$len ; $i++ ) {
			$out[] = $a[$i][$prop];
		}

		return $out;
	}
    /**
	 * Searching / Filtering
	 *
	 * Construct the WHERE clause for server-side processing SQL query.
	 *
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here performance on large
	 * databases would be very poor
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $columns Column information array
	 *  @param  Builder $query laravel query builder object to add the filtering statements to
	 *  @return string SQL where clause
	 */
	static function filter ( $request, $columns, $query)
	{
        $query->where(function($query) use($columns,$request) {
                
            
		$globalSearch = array();
		$columnSearch = array();
		$dtColumns = self::pluck( $columns, 'dt' );
                $newstring = "";
		if ( isset($request['search']) && $request['search']['value'] != '' ) {
			$str = $request['search']['value'];

			for ( $i=0, $ien=count($request['columns']) ; $i<$ien ; $i++ ) {
				$requestColumn = $request['columns'][$i];
				$columnIdx = array_search( $requestColumn['data'], $dtColumns );
				$column = $columns[ $columnIdx ];

				if ( $requestColumn['searchable'] == 'true' ) {
					//$binding = self::bind( $bindings, '%'.$str.'%', PDO::PARAM_STR );
					//$globalSearch[] = "`".$column['db']."` LIKE ".$binding;
					if($column['db']==='main_agent') {
						$query->orWhereHas('mainagent', function($query) use ($str) {
							$query->WhereHas('user', function($query) use($str) {
								$query->where('first_name','LIKE','%'.$str.'%')->orWhere('last_name','LIKE','%'.$str.'%');
							});
						});
					} elseif($column['db']==='influencers') {
                    
                    } else {
						$query->orWhere($column['db'],'LIKE','%'.$str.'%');
					}
				}
			}
		}
                
		// Individual column filtering
		/*for ( $i=0, $ien=count($request['columns']) ; $i<$ien ; $i++ ) {
			$requestColumn = $request['columns'][$i];
			$columnIdx = array_search( $requestColumn['data'], $dtColumns );
			$column = $columns[ $columnIdx ];

			$str = $requestColumn['search']['value'];

			if ( $requestColumn['searchable'] == 'true' &&
			 $str != '' ) {
				$binding = self::bind( $bindings, '%'.$str.'%', PDO::PARAM_STR );
				$columnSearch[] = "`".$column['db']."` LIKE ".$binding;
			}
		}*/

		// Combine the filters into a single string
		/*$where = '';

		if ( count( $globalSearch ) ) {
			$where = '('.implode(' OR ', $globalSearch).')';
		}

		if ( count( $columnSearch ) ) {
			$where = $where === '' ?
				implode(' AND ', $columnSearch) :
				$where .' AND '. implode(' AND ', $columnSearch);
		}

		if ( $where !== '' ) {
			$where = 'WHERE '.$where;
		}

		return $where;*/
                
                return $query;
            });
            return $query;
	}
}