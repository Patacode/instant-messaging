<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

use App\Models\Message;
use App\Models\User;

use App\Events\MessageSent;

/**
 * Controller used to handle chat requests.
 */
class ChatsController extends Controller
{

    private $current_contact_id;

    /**
     * Constructs a new ChatController only for authenticated user.
     */
    public function __construct(){
        $this -> middleware('auth');
    }

    /**
     * Initiates the chat for the current user and the user denoted by the given id (as param) by returning
     * a chat view with the proper informations.
     *
     * @param id the parameter to be used (contact id here)
     *
     * @return a chat view for the current user and the proper contact.
     */
    public function chatInit($id){
        if(!$id || !intval($id)):
            return view('error', ['message' => 'Invalid id']);
        elseif($id == Auth::user() -> id):
            return view('error', ['message' => 'You cannot chat with yourself']);
        elseif(empty(Auth::user() -> contacts() -> where('contact_id', $id) -> get() -> toArray())):
            return view('error', ['message' => 'Impossible to chat with a person not in your contact list']);
        endif;

        session(['current_contact_id' => $id]);

        return view('chat', [
            "fclient" => Auth::user(), 
            "sclient" => User::find($id), 
            "messages" => $this -> chatGet($id)
        ]);
    }

    /**
     * Fetches all the messages sent by the current user to the user denoted by the given id (as param) and all
     * the messages sent by him to the current user.
     *
     * @param id the parameter to be used (contact id here)
     *
     * @return an array containing the proper messages.
     */
    public function chatGet($id){
        if(!$id || !intval($id)):
            return ['error' => 'Invalid id'];
        elseif(empty(Auth::user() -> contacts() -> where('contact_id', $id) -> get() -> toArray())):
            return ['error' => 'You are not allowed to retrieve messages for this user, he is not in your contact list'];
        endif;

        $messages = Message::where('user_id', $id) 
            -> where('receiver_id', Auth::user() -> id) 
            -> orWhere('user_id', Auth::user() -> id) 
            -> where('receiver_id', $id)
            -> join('users', 'users.id', '=', 'messages.user_id') 
            -> orderBy('messages.created_at')
            -> get(['messages.id', 'messages.message', 'messages.updated_at', 'users.name', 'users.email']) 
            -> toArray();

        foreach($messages as &$message):
            $message['message'] = Crypt::decryptString($message['message']);
        endforeach;

        return $messages;
    }

    /**
     * Adds the message contained in the given request into database and broadcast it to the clients.
     *
     * @param request the request to be used
     *
     * @return status informations.
     */
    public function chatAdd(Request $request){
        if($request -> input('receiver_id') != session('current_contact_id')):
            return ['error' => 'Unexpected receiver'];
        endif;

        // verify the signature
        $user = Auth::user();
        $message = $user -> messages() -> create([
            'message' => Crypt::encryptString($request -> input('message')),
            'receiver_id' => $request -> input('receiver_id'),
        ]);

        $message = [
            "id" => $message['id'],
            "message" => $request -> input('message'), 
            "receiver_id" => $request -> input('receiver_id'), 
            "signature" => $request -> input('signature'),
        ];

        broadcast(new MessageSent($user, User::find($request -> input('receiver_id')), json_encode($message)));
        return ['success' => 'Message sent !'];
    }

    public function chatDelete(Request $request){
        Message::where('id', $request -> input('id')) -> delete();
        return ['success' => 'Message successfully removed'];
    }

    /**
     * Executes a chat request by calling the method denoted by the given action. Each action is automatically
     * preceded by "chat" and capitalized. If no method is found in this controller after transformation, nothing 
     * is done.
     * 
     * @param action the action to perform
     * @param param the request parameter to be passed to the action
     *
     * @return the return value of the executed action or null if not action is found.
     */
    public function processChatRequest($action, $param){
        $procedure = "chat" . ucfirst($action);
        if(method_exists($this, $procedure)){
            return $this -> $procedure($param);
        }
    }

    /**
     * Executes a post chat request by calling the method denoted by the given action. Each action is automatically
     * preceded by "chat" and capitalized. If no method is found in this controller after transformation, nothing 
     * is done.
     * 
     * @param request the request object to be used
     * @param action the action to perform
     *
     * @return the return value of the executed action or null if not action is found.
     */
    public function processChatPostRequest(Request $request, $action){
        $procedure = "chat" . ucfirst($action);
        if(method_exists($this, $procedure)){
            return $this -> $procedure($request);
        }
    }
}

