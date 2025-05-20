<?php

namespace App\Http\Controllers\Api;

use App\Models\Contact;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function index()
    {
        return response()->json(Contact::all(), 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name'  => 'required|string|max:255',
            'last_name' => 'required|string|min:3|max:255',
            'phone' => 'required|string|min:3|max:20',
            'description' => 'nullable|string|min:3',
            'email' => 'nullable|email|unique:contacts,email',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:5048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('contacts', 'public');
            $data['image'] = $path;
        }

        $contact = Contact::create($data);

        return response()->json($contact, 201);
    }

    public function show($id)
    {
        $contact = Contact::find($id);
        if (!$contact) {
            return response()->json(['message' => 'Contacto no encontrado'], 404);
        }
        return response()->json($contact, 200);
    }

    public function update(Request $request, $id)
    {
        $contact = Contact::find($id);
        if (!$contact) {
            return response()->json(['message' => 'Contacto no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'first_name'  => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|min:3|max:255',
            'phone' => 'sometimes|string|min:3|max:20',
            'description' => 'nullable|string|min:3',
            'email' => 'nullable|email|unique:contacts,email',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:5048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();


        if ($request->hasFile('image')) {
            // Eliminar imagen anterior si existe
            if ($contact->image && \Storage::disk('public')->exists($contact->image)) {
                \Storage::disk('public')->delete($contact->image);
            }

            $path = $request->file('image')->store('contacts', 'public');
            $data['image'] = $path;
        }

        $contact->update($data);

        return response()->json($contact, 200);
    }

    public function destroy($id)
    {
        $contact = Contact::find($id);
        if (!$contact) {
            return response()->json(['message' => 'Contacto no encontrado'], 404);
        }

        $contact->delete();

        if ($contact->image && \Storage::disk('public')->exists($contact->image)) {
            \Storage::disk('public')->delete($contact->image);
        }
        return response()->json(['message' => 'Contacto eliminado'], 200);
    }
}
