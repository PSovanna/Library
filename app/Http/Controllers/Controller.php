<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

abstract class Controller
{
    protected function success(string $message, ?Model $record = null, int $status = 200)
    {
        return response()->json(array_merge($record?->toArray() ?? [], [
            'success' => true, 'message' => $message,
        ]), $status);
    }

    protected function saveWithPhoto(Model $record, Request $request, array $data, string $directory): Model
    {
        $oldPhoto = $record->Photo;
        $newPhoto = null;
        unset($data['Photo']);
        try {
            if ($request->hasFile('Photo')) {
                $newPhoto = $request->file('Photo')->store($directory, 'public');
                if (! $newPhoto) {
                    throw new \RuntimeException('Picture upload failed.');
                }
                $data['Photo'] = $newPhoto;
            }
            $record->fill($data)->save();
        } catch (\Throwable $exception) {
            $this->deletePhoto($newPhoto);
            throw $exception;
        }
        if ($newPhoto) {
            $this->deletePhoto($oldPhoto);
        }

        return $record->refresh();
    }

    protected function deletePhoto(?string $path): void
    {
        if ($path && preg_match('#^(authors|books)/[^/]+$#', $path)) {
            try {
                Storage::disk('public')->delete($path);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }
    }
}
