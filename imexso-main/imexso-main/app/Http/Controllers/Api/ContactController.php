<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ContactStoreRequest;
use App\Mail\ContactSubmissionMail;
use App\Models\ContactSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(ContactStoreRequest $request): JsonResponse
    {
        $submission = ContactSubmission::query()->create($request->validated());

        $inbox = config('imexso.contact_inbox');

        if (is_string($inbox) && $inbox !== '') {
            Mail::to($inbox)->send(new ContactSubmissionMail($submission));
        }

        return response()->json([
            'message' => 'Your message has been sent successfully.',
        ]);
    }
}
