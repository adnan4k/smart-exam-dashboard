<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Subscription;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function contact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string',
        ]);
        $contact = new Contact();
        $contact->name  = $request->name;
        $contact->subject = $request->subject;
        $contact->email = $request->email;
        $contact->message = $request->message;
        $contact->type = 'contact';
        $contact->save();


        return response()->json([
            'success' => true,
            'message' => 'Thank you for contacting us!',
            'data' => $contact
        ], 201);
    }
    public function partnership(Request $request)
    {
        $validated = $request->validate([
            'link' => 'nullable',
            'email' => 'nullable',
            'phone' => 'required',
            'company_name' => 'required',
            'message' => 'required',

        ]);
        $partnership =  new Contact();
        $partnership->link = $request->link;
        $partnership->email = $request->email;
        $partnership->company_name = $request->company_name;
        $partnership->message = $request->message;
        $partnership->phone = $request->phone;
        $partnership->type = 'partnership';

        $partnership->save();

        return response()->json([
            'success' => true,
            'message' => 'Thank you for contacting us!',
            'data' => $partnership,
        ], 201);
    }

    public function subscribe(Request $request){
        $subscription = new Subscription();
        $subscription->email = $request->email;
        $subscription->save();
        return response()->json(200);
    }
}
