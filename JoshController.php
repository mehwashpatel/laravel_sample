<?php namespace App\Http\Controllers;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Redirect;
use Sentry;
use Cartalyst\Sentry\Users\LoginRequiredException;
use Cartalyst\Sentry\Users\PasswordRequiredException;
use Cartalyst\Sentry\Users\UserExistsException;
use Cartalyst\Sentry\Users\UserNotFoundException;
use View;
use App\Category;
use App\Platform;
use App\Industry;

use App\Services\PermissionService;

class JoshController extends Controller {

	/**
	* Crop Demo
	*/
	public function crop_demo()
	{
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$targ_w = $targ_h = 150;
			$jpeg_quality = 99;

			$src = base_path().'/public/assets/img/cropping-image.jpg';
		//dd($src);
			$img_r = imagecreatefromjpeg($src);

			$dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

			imagecopyresampled($dst_r,$img_r,0,0,intval($_POST['x']),intval($_POST['y']), $targ_w,$targ_h, intval($_POST['w']),intval($_POST['h']));

			header('Content-type: image/jpeg');
			imagejpeg($dst_r,null,$jpeg_quality);

			exit;
		}
	}

	/**
     * Message bag.
     *
     * @var Illuminate\Support\MessageBag
     */
    protected $messageBag = null;

    /**
     * Initializer.
     *
     * @return void
     */
    public function __construct()
    {
        // CSRF Protection
        $this->beforeFilter('csrf', array('on' => 'post'));

        //
        $this->messageBag = new MessageBag;
    }

    public function showHome()
    {
    	if(Sentry::check()) {
			ini_set('memory_limit', '-1');
			//$noticerepo = new \App\Repositories\NotificationRepository();
			//$notifications = $noticerepo->findByRecipient(config('admin_accounts.brandfit_admin_id'));
			$notifications = \App\AdminNotification::latest()->take(20)->get();
			
			$permissions = new PermissionService();
			$permissionsArray = $permissions->getPermissions();
			$permissionsArray['notifications'] = $notifications;
			//dd($permissionsArray);
			return View('admin/index', $permissionsArray);
		}
		else {
			
			return Redirect::to('admin/signin')->with('error', 'You must be logged in!');
		}
    }

    public function showView($name=null)
    {

    	if(View::exists('admin/'.$name))
		{
			if(Sentry::check())
				return View('admin/'.$name);
			else
				return Redirect::to('admin/signin')->with('error', 'You must be logged in!');
		}
		else
		{
			return View('admin/404');
		}
    }


}