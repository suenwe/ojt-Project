<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserPhotoController extends Controller
{
    /**
     * Send a member's profile photo so feeds can show it without embedding the image in every post.
     * The browser keeps a copy and only downloads it again when the photo changes (ETag).
     */
    public function __invoke(Request $request, User $user): Response
    {
        abort_if($user->isBanned() && $request->user()->role !== 'admin', 404);

        $photoFile = $user->photoFile();

        abort_if($photoFile === null, 404);

        $response = response($photoFile['bytes'], 200, [
            'Content-Type' => $photoFile['mimeType'],
            'Cache-Control' => 'private, no-cache',
        ]);
        $response->setEtag(md5($photoFile['bytes']));
        $response->isNotModified($request);

        return $response;
    }
}
