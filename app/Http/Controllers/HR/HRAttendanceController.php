<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HRAttendanceController extends Controller
{
   public function __invoke(Request $request): Response
   {
      return Inertia::render('HR/Index', ['activeTab' => 'attendance']);
   }
}
