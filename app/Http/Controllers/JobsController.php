<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginate;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\JobApplication;
use App\Models\Category;
use App\Models\JobType;
use App\Models\Job;
use App\Models\SavedJob;
use App\Mail\JobNotificationEmail;

class JobsController extends Controller
{
    //showJob method
    public function index(Request $request){
      
     $categories=Category::where('status',1)->get(); 
     
     $jobTypes=JobType::where('status',1)->get(); 

     $jobs=Job::where('status',1);

    //  search job on basis of keyword
    if(!empty($request->keyword)){
        $jobs=$jobs->where(function($query) use($request){
            $query->orWhere('title','like','%'.$request->keyword.'%');
            $query->orWhere('keywords','like','%'.$request->keyword.'%');

        });
    }

    // searching job based on location

    if(!empty($request->location)){
        $jobs=$jobs->where('location',$request->location);
    }


    // searching job on basis of category
    if(!empty($request->category)){
        $jobs=$jobs->where('category_id',$request->category);
    }
     
    $jobTypeArray=[];
    // searching job on basis of jobType
    if(!empty($request->jobType)){
        $jobTypeArray=explode(',',$request->jobType);
        $jobs=$jobs->whereIn('job_type_id',$jobTypeArray);
    }

     // searching job on basis of experience
     if(!empty($request->experience)){
        $jobs=$jobs->where('experience',$request->experience);
    }




     $jobs = $jobs->with(['jobType','category']);

     if($request->sort=='0'){
     
        $jobs=$jobs->orderBy('created_at','ASC');


     }
     else{

        $jobs=$jobs->orderBy('created_at','DESC');
     }
     
    
     
     $jobs=$jobs->paginate(9);



     return view('front.jobs',[
        'categories' => $categories,
        'jobTypes' => $jobTypes,
        'jobs' => $jobs,
        'jobTypeArray' => $jobTypeArray
     ]);
    }

    // jobDetail will show jobdetails page
    public function detail($id){

        $job = Job::where([
            'id'=>$id,
            'status'=>1
        ])->with(['jobType','category'])->first();

     //if job not exists then 404 page 
    if($job == null){
        abort(404);
    }

    $count=0;
    if(Auth::user()){
       // check if user have already saved job
        $count = SavedJob::where([
            'user_id' => Auth::user()->id,
            'job_id' => $id
        ])->count();

    }


    // fetching Applicants
    $applications = JobApplication::where('job_id',$id)->with('user')->get();
    // dd($applications);

   
    

        return view('front.jobDetail',['job' => $job,
                                      'count' => $count, 
                                      'applications' => $applications
                                    ]);

    }

    //ApplyJob
    public function applyJob(Request $request){
    
        $id=$request->id;

        $job=Job::where('id',$id)->first();


        // if job not found in db
        if($job==null){
            session()->flash('error','Job Does not exists');
            return response()->json([
                'status' => false,
                'message' => 'Job Does Not exists'
            ]);
        }

        // you can't apply on your own posted job
        $employer_id=$job->user_id;

        if($employer_id==Auth::user()->id){
            session()->flash('error','you can not apply on your own posted job');
            return response()->json([
                'status' => false,
                'message' => 'you can not apply on your own posted job'
            ]);

        }

        // You can't apply on a single job twice
        $jobApplicationCount=JobApplication::where([
            'user_id' => Auth::user()->id,
            'job_id' => $id
        ])->count();

        if( $jobApplicationCount>0){
            session()->flash('error','You have already applied for this job');
            return response()->json([
                'status' => false,
                'message' => 'You have already applied for this job'
            ]);
        }

        $application = new JobApplication();
        $application->job_id=$id;
        $application->user_id=Auth::user()->id;
        $application->employer_id=$employer_id;
        $application->applied_date=now();
        $application->save();

        // Send Notofiaction email to employer
        // $employer = User::where('id',$employer_id)->first();

        // $mailData = [
        //     'employer' => $employer,
        //     'user' => Auth::user(),
        //     'job' => $job,
        // ];

        // Mail::to($employer->email)->send(new JobNotificationEmail($mailData));

        session()->flash('success','You have succesfully applied for job');
        return response()->json([
            'status' => true,
            'message' => 'You have succesfully applied for job'
        ]);


    }

    // SaveJob
    public function saveJob(Request $request){

        $id=$request->id;
        $job = Job::find($id);

        if($job==null){
            session()->flash('error','Job Not Found');

            return response()->json([
                'status' => false,
            ]);
        }

        // check if user have already saved job
       $count = SavedJob::where([
            'user_id' => Auth::user()->id,
            'job_id' => $id
        ])->count();


        if($count>0){
            session()->flash('error','You have already Saved this Job');

            return response()->json([
                'status' => false,
            ]);
        }
         
        $savedJob = new SavedJob;
        $savedJob->job_id = $id;
        $savedJob->user_id = Auth::user()->id;
        $savedJob->save();

        session()->flash('success','Job Saved Successfully');

        return response()->json([
            'status' => true,
        ]);



    }
}
