<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class FileUploadService
{

    // uploade single file
    public function uploadFile($request, $name, $model)
    {
        if ($request->file($name) != null) {
            $image = $request->file($name);
            if ($model->getFirstMedia() != null) {
                $model->getFirstMedia()->delete();
            }
            $file_name = $name . "_" . $model->id . "_" . date("Ymdhis") . ".jpg";
            $media = $model->addMedia($image)
                ->usingFileName($file_name)
                ->toMediaCollection($name);
            $model[$name] = $media->id;
            $model->save();
        }
    }

    // uploade Multiple files
    public function uploadFiles($request, $name, $model)
    {
        if ($request->file($name) != null) {
            Log::info($request->file($name));
            $cards = [];
            foreach ($request->file($name) as $file) {
                $file_name = $name . "_" . $model->id . "_" . date("Ymdhis") . ".jpg";
                $media = $model->addMedia($file)
                    ->usingFileName($file_name)
                    ->toMediaCollection($name);

                array_push($cards, $media->id);
            }
            Log::info($cards);
            $model[$name] = json_encode($cards);
            $model->save();
        }
    }
}
