<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Facades\StaffTraining;
use App\Models\TrainingFeedback; 
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Auth;

class StaffTrainingController extends Controller
{
    
    public function index()
    {
        $trainings = StaffTraining::getMyTrainings();
        return view('staff.trainings.index', compact('trainings'));
    }

       //===========================================================================================

    
public function storeFeedback(Request $request)
{
    $request->validate([
        'training_program_id' => 'required|exists:training_programs,id',
        'rating' => 'required|integer|min:1|max:5',
        'comment' => 'required|string|max:1000',
    ]);

  
    TrainingFeedback::create([
        'user_id'             => Auth::id(), 
        'training_program_id' => $request->training_program_id,
        'rating'              => $request->rating,
        'comments'            => $request->comment, 
    ]);

    return redirect()->back()->with('success', 'Thank you! Your feedback has been submitted.');
}
}