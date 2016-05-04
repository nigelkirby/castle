<?php

namespace Castle;

use File;
use Illuminate\Http\UploadedFile;
use Log;
use Storage;
use Traversable;

trait Attachable
{

	// sorta-abstract public function getAttachmentDirectoryAttribute() { }

	public function setAttachmentsAttribute($value)
	{
		$storage = Storage::disk('attachments');
		$attachments = [];

		$directory = method_exists($this, 'getAttachmentDirectoryAttribute') ?
			$this->getAttachmentDirectoryAttribute() :
			'';

		if (!$value) {
			return $this->attributes['attachments'] = json_encode([]);
		}

		if (!(is_array($value) or $value instanceOf Traversable)) {
			$value = [$value];
		}

		foreach ($value as $file) {
			if ($file instanceOf UploadedFile and $file->isValid()) {
				try {
					$name = $directory . '/' . utf8_encode($file->getClientOriginalName());
					$contents = file_get_contents($file->getRealPath());

					if ($storage->has($name)) {
						Log::info('overwriting existing attachment "'.$name.'"');
					}

					if (in_array($name, $attachments) !== false) {
						$index = array_search($name, $attachments);
						unset($attachments[$index]);
					}

					$storage->put($name, $contents);
				} catch (\Exception $exception) {
					Log::info('could not upload attachment: '.$exception->getMessage());
				} finally {
					File::delete($file->getRealPath());
					$file = $name;
				}
			}

			if (!in_array($file, $attachments)) {
				$attachments[] = $file;
			}
		}

		return $this->attributes['attachments'] = json_encode($attachments);
	}

}
