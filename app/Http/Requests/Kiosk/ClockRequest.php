<?php

namespace App\Http\Requests\Kiosk;

use Illuminate\Foundation\Http\FormRequest;

class ClockRequest extends FormRequest
{
   public function authorize(): bool
   {
         return true;
   }

   public function rules(): array
   {
      return [
         'user_id' => ['nullable', 'uuid', 'required_without_all:pin,rfid_code,qr_code_data,face_recognition_hash'],
         'pin' => ['nullable', 'digits:6', 'required_without_all:user_id,rfid_code,qr_code_data,face_recognition_hash'],
         'rfid_code' => ['nullable', 'string', 'max:150', 'required_without_all:user_id,pin,qr_code_data,face_recognition_hash'],
         'qr_code_data' => ['nullable', 'string', 'max:500', 'required_without_all:user_id,pin,rfid_code,face_recognition_hash'],
         'face_recognition_hash' => ['nullable', 'string', 'max:255', 'required_without_all:user_id,pin,rfid_code,qr_code_data'],
         'latitude' => ['required', 'numeric', 'between:-90,90'],
         'longitude' => ['required', 'numeric', 'between:-180,180'],
      ];
   }
}
