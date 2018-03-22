<?php namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Sentry;
use Cartalyst\Sentry\Users\LoginRequiredException;
use Cartalyst\Sentry\Users\PasswordRequiredException;
use Cartalyst\Sentry\Users\UserExistsException;
use Cartalyst\Sentry\Users\UserNotFoundException;
use View;
use Validator;
use Input;
use Session;
use Redirect;
use Lang;
use URL;
use Mail;
use File;
use App\User;
use App\MainUser;
use App\Agent;
use App\Agency;

use App\Repositories\AgencyRepository;
use App\Repositories\InfluencerRepository;

use Storage;
use Response;
use App\Country;

use App\Services\PermissionService;

class AgencyController extends JoshController
{
   
    /**
     * Declare the rules for the form validation
     *
     * @var array
     */
     

    /**
     * Agency create form processing.
     *
     * @return Redirect
     */
    public function postCreate()
    {
        // Declare the rules for the form validation
        $rules = array(
			'company_name'		=> 'required',
			'phone_number'		=> 'required',
            'first_name'       	=> 'required|min:3',
            'last_name'        	=> 'required|min:3',
            'email'            	=> 'required|email|unique:users,email',
            'contact_email'     => 'required|email',
            'password'         	=> 'required|between:3,32',
            'password_confirm' 	=> 'required|same:password',
            //'pic'              => 'mimes:jpg,jpeg,bmp,png|max:10000'
        );
        $verifier = \App::make('validation.presence');
        $verifier->setConnection('mysql_main');
        
        // Create a new validator instance from our validation rules
        $validator = Validator::make(Input::all(), $rules);
        $validator->setPresenceVerifier($verifier);
        

        // If validation fails, we'll exit the operation now.
        if ($validator->fails()) {
            // Ooops.. something went wrong
            return Redirect::back()->withInput()->withErrors($validator);
        }

        

        try {
			$data = Input::only('first_name','last_name','email','password','contact_email');
			$data['username'] = $data['email'];
			$data['role'] = config('roles.agent');
			$mainuserrepo = new \App\Repositories\UserRepository();
			$newuser = $mainuserrepo->create($data);
            

			$agencyrepo = new \App\Repositories\AgencyRepository();
			$agencydata = Input::only('company_name','phone_number','special_remarks','country','province','city','address','postal_code');
			$agencydata['verified'] = Input::get('verified')?1:0;
			$agencydata['approved'] = Input::get('approved')?1:0;
			$agencydata['main_agent_id'] = $newuser->getusertype->id;
			$newagency = $agencyrepo->create($agencydata);
            
			$newuser->getusertype->agency()->associate($newagency);       
            $newuser->push();            
            
			// Redirect to the home page with success menu
            return Redirect::route("agencies")->with('success', Lang::get('users/message.success.create'));

        } catch (LoginRequiredException $e) {
            $error = Lang::get('admin/users/message.user_login_required');
        } catch (PasswordRequiredException $e) {
            $error = Lang::get('admin/users/message.user_password_required');
        } catch (UserExistsException $e) {
            $error = Lang::get('admin/users/message.user_exists');
        }

        // Redirect to the user creation page
        return Redirect::back()->withInput()->with('error', $error);
    }

   

    /**
     * Get user access state
     *
     * @return View
     */
    public function getUserAccess()
    {
        if (Sentry::getUser()->hasAccess('admin')) {

            $userAccess = "admin";
        }
        else {
            $userAccess = "others";
        }

        // Show the page
        return View('admin/groups/any_user', compact('userAccess'));
    }
}
