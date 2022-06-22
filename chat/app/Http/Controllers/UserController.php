<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Contact;

use App\Events\ContactRequested;
use App\Events\ResponseAccepted;
use App\Events\ResponseDenied;
use App\Events\ContactRemoved;
use Illuminate\Support\Facades\DB;

/**
 * Controller used to handle user requests.
 */
class UserController extends Controller
{

    /**
     * Constructs a new UserController only for authenticated user.
     */
    public function __construct(){
        $this -> middleware('auth');
    }

    /**
     * Returns the index view of this controller.
     *
     * @return the contact view
     */
    public function index(){
        return view('contact');
    }

    /**
     * Fetches contacts of the user denoted by the given id or of the current user if given id is empty.
     *
     * @param id optional id of the user for whom to retrieve contacts (not used here)
     *
     * @return an array containing the contacts of proper user.
     */
    public function userContacts($id = null){
        $user = Auth::user();
        return $user -> contacts() -> join('users', 'users.id', '=', 'contacts.contact_id') -> get() -> toArray(); 
    }

    /**
     * Fetches informations about the user denoted by the given id of the current user if given id is empty.
     *
     * @param id optional id of the user for whom to retrieve informations
     *
     * @return an object containing informations of proper user.
     */
    public function userInfo($id = null){
        return $id ? User::find($id) : Auth::user();
    }

    /**
     * Fetches all the users present in database except the one denoted by the given id or of the current user if given id is empty.
     *
     * @param id optional id of the user to exclude
     *
     * @return an array containing all the subscribed users.
     */
    public function userAll($id = null){
        $exclude = $id ?: Auth::user() -> id;
        return User::where('users.id', '!=', $exclude) -> get() -> toArray();
    }

    /**
     * Handles contact request by broadcasting a ContactRequest event to the clients.
     *
     * @param request the request object to be used
     * @param param the request parameter (not used here)
     *
     * @return status informations.
     */
    public function userRequest(Request $request, $param = null){
        if(!User::where('id', $request -> input('receiver_id')) -> first()):
            return ['error' => 'Unexpected receiver'];
        endif;

        broadcast(new ContactRequested(Auth::user(), $request -> input('receiver_id'))) -> toOthers();
        return ['success' => 'Contact request sended'];
    }

    /**
     * Handles contact response by creating contact links for success response and by broadcasting
     * proper event in either case (success or deny response). The response will be interpreted
     * as a success if given parameter is "true" or as a deny if given parameter is "false".
     *
     * @param request the request object to be used
     * @param param the request parameter. Only "true" or "false" here will be accepted.
     *
     * @return status informations.
     */
    public function userResponse(Request $request, $param = null){
        $event = null;
        if($param === "true"): 
            $user = Auth::user();
            Contact::create([
                'user_id' => $user -> id,
                'contact_id' => $request -> input('receiver_id')
            ]);
            Contact::create([
                'user_id' => $request -> input('receiver_id'),
                'contact_id' => $user -> id 
            ]);
            $event = new ResponseAccepted($user, $request -> input('receiver_id'));
        elseif($param === "false"): $event = new ResponseDenied(Auth::user(), $request -> input('receiver_id'));
        endif;

        if($event)  
            broadcast($event);

        return ['success' => 'Contact response sended'];
    }

    /**
     * Removes the contact received in the given request from the contact list of the current user and do
     * it for the contact too (removes current user from the contact list of the received contact).
     *
     * @param request the request object to be used
     * @param param the request parameter (not used here)
     *
     * @return status informations.
     */
    public function userRemove(Request $request, $param = null){
        if(!Auth::user() -> contacts() -> where('contact_id', $request -> input('receiver_id')) -> get() -> toArray()):
            return ['error' => 'Unexpected contact'];
        endif;

        Auth::user() -> contacts() -> where('contact_id', $request -> input('receiver_id')) -> delete();
        Contact::where(['user_id' => $request -> input('receiver_id'), 'contact_id' => Auth::user() -> id]) -> delete();
        broadcast(new ContactRemoved(Auth::user(), $request -> input('receiver_id')));
        return ['success' => 'Successfully removed'];
    }

    /**
     * Stores the received public key in database for the current user.
     *
     * @param request the request object to be used (containing public key)
     * @param param the request parameter (not used here)
     *
     * @return status informations.
     */
    public function userKey(Request $request, $param = null){
        $user = Auth::user();
        $user -> public_key = $request -> input('publicKey');
        $user -> save();
        return ['success' => 'Public key successfully stored'];
    }

    /**
     * Executes a user request by calling the method denoted by the given action. Each action is automatically
     * preceded by "user" and capitalized. If no method is found in this controller after transformation, nothing 
     * is done.
     *
     * Available actions are:
     *  - contacts: to retrieve a contact list
     *  - info: to retrieve informations about a specific user
     *  - all: to retrieve informations about all the users
     *
     * @param action the action to perform
     * @param param the the request parameter to be passed to the action
     * 
     * @return the return value of the executed action or null if not action is found.
     */
    public function processUserRequest($action, $param = null){
        $procedure = "user" . ucfirst($action);
        if(method_exists($this, $procedure)):
            return $this -> $procedure($param);
        endif;
    }

    // $action = ['request', 'response', 'remove']
    /**
     * Executes a user post request by calling the method denoted by the given action. Each action is automatically
     * preceded by "user" and capitalized. If no method is found in this controller after transformation, nothing 
     * is done.
     *
     * Available actions are:
     *  - request: to handle a contact request for a specific user
     *  - response: to handle a contact response for a specific user
     *  - remove: to remove a contact from his contact list
     *  - key: to store public key
     *
     * @param request the request to be passed to the action
     * @param action the action to perform
     * @param param the request parameter to be passed to the action
     *
     * @return the return value of the executed action or null if not action is found.
     */
    public function processUserPostRequest(Request $request, $action, $param = null){
        $procedure = "user" . ucfirst($action);
        if(method_exists($this, $procedure)):
            return $this -> $procedure($request, $param);
        endif;
    }
}

