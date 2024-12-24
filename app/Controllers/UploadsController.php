<?php

namespace App\Controllers;

use Illuminate\Http\Request;

class UploadsController {
  function create(Request $request) {
    $user = \Auth::user();
    $request->validate(['file' => ['required', 'max:2048']]);
    $file = $request->file('file');
    return \Storage::putFileAs(
      'uploads/' . $user->id,
      $file,
      $file->getClientOriginalName(),
    );
  }

  function destroy(Request $request) {
    $user = \Auth::user();
    $filename = $request->input('filename');
    $path = 'uploads/'. $user->id . '/' . $filename;
    if (!\Storage::exists($path))
      return abort(404, 'file does not exist');
    \Storage::delete($path);
    return $path;
  }
}