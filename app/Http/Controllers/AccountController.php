<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Paginate;
use App\Models\User;
use App\Models\Category;
use App\Models\JobType;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\SavedJob;







class AccountController extends Controller
{
    //this function to handle user registeration
    public function registration(){
     return view('front.Account.registration');
    }
    

    // this function will save a user
    public function processRegistration(Request $request){
        $validator=Validator::make($request->all(),[
            'name'=>'required',
            'email'=>'required|email|unique:users,email', //making email unique 
            'password'=>'required|min:6|same:confirm_password',
            'confirm_password'=>'required'
        ]);

        if($validator->passes()){

        //entering new user in user model
        $user = new User();
        $user->name=$request->name;
        $user->email=$request->email;
        $user->password=Hash::make($request->password);
        $user->save();

        session()->flash('success','Registration Successfull! Redirected to Login Page');


            return response()->json([
                'status'=>true,
                'errors'=>[]
            ]);
        }
        else{

            return response()->json([
                'status'=>false,
                'errors'=>$validator->errors()
            ]);

        }
    }

     //this function to handle user login
     public function login(){

        return view('front.Account.login');
    
     }

    //  Authenticate User

    public function authenticate(Request $request){
       $validator=Validator::make($request->all(),[
        'email'=>'required|email',
        'password'=>'required'
       ]);
       if($validator->passes()){
       if(Auth::attempt(['email' => $request->email, 'password'=>$request->password])){
             return redirect()->route('account.profile');
       }
       else{
        return redirect()->route('account.login')->with('error','Either Email/Password is Incorrect');
       }
       }
       else{

        return redirect()->route('account.login')
               ->withErrors($validator)
               ->withInput($request->only('email'));


       }
    }

    // Profile Page
    public function profile(){

       $id = Auth::user()->id;

       $user=User::where('id',$id)->first();

       
       return view('front.account.profile',[
        'user'=>$user
       ]);
    }

    // function to update profile
    public function updateProfile(Request $request){

        $id=Auth::user()->id;

        $validator=Validator::make($request->all(),[
          'name'=>'required|min:4|max:20',
          'email'=>'required|email|unique:users,email,'.$id.',id'
        ]);

        if($validator->passes()){
          
            $user=User::find($id);
            $user->name=$request->name;
            $user->email=$request->email;
            $user->mobile=$request->mobile;
            $user->designation=$request->designation;
            $user->save();

            session()->flash('success','Profile Updated Succcessfully');



            return response()->json([
                'status'=>true,
                'errors'=>[]
            ]);
        }

        else{

            return response()->json([
                'status'=>false,
                'errors'=>$validator->errors()
            ]);

        }

    }

    // logout
    public function logout(){
        Auth::logout();
        
        return redirect()->route('account.login');
    }

    // profile pic update

    public function updateProfilePic(Request $request){

        $id=Auth::user()->id;


        $validator = Validator::make($request->all(),[
        'image'=> 'required|image'
        ]);

        if($validator->passes()){

            $image=$request->image;
            $ext=$image->getClientOriginalExtension();
            // appending idtime to image as name
            $imageName=$id.'-'.time().'.'.$ext;
            $image->move(public_path('/Profile_img'), $imageName);

            // updating image in db
            User::where('id',$id)->update(['image' => $imageName]);

            session()->flash('success','Profile Picture Updated Successfully');

            return response()->json([
                'status' => true,
                'errors' => []
            ]);

        }
        else{

            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

    }

    // createJob function
    public function createJob(){

        $categories = Category::orderBy('name','ASC')->where('status',1)->get();

        $jobTypes = JobType::orderBy('name','ASC')->where('status',1)->get();

        return view('front.account.job.create',[
            'categories' => $categories,
            'jobTypes' => $jobTypes
        ]);

    }

    // validating createJob form
    public function saveJob(Request $request){

        $rules=[
            'title' => 'required|min:5|max:200',
            'category' => 'required',
            'jobType' => 'required',
            'vacancy' => 'required|integer|min:1',
            'location' => 'required|max:50',
            'description' => 'required',
            'company_name' => 'required|min:4|max:50',
        ];
        $validator = Validator::make($request->all(),$rules);

        if($validator->passes()){
           
         $job = new Job();
         $job->title = $request->title;
         $job->category_id = $request->category;
         $job->job_type_id = $request->jobType;
         $job->user_id = Auth::user()->id;
         $job->vacancy = $request->vacancy;
         $job->salary = $request->salary;
         $job->location = $request->location;
         $job->description = $request->description;
         $job->benefits = $request->benefits;
         $job->responsibility = $request->responsibility;
         $job->qualifications = $request->qualifications;
         $job->keywords= $request->keywords;
         $job->experience = $request->experience;
         $job->company_name = $request->company_name;
         $job->company_location = $request->company_location;
         $job->company_website = $request->company_website;
         $job->save();

          
        session()->flash('success','Job Posted successfully');

         return response()->json([
            'status' => true,
            'errors' =>[]
        ]);


        }


        else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    // postingJob

    public function myJobs(){

        $jobs=Job::where('user_id',Auth::user()->id)->with('jobType')->orderBy('created_at','DESC')->paginate(5);
        // dd($jobs);
        return view('front.account.job.my-jobs',[
            'jobs'=>$jobs
        ]);
    }

    // editJob

    public function editJob(Request $request,$jobId){

        // dd($jobId);

        $categories = Category::orderBy('name','ASC')->where('status',1)->get();

        $jobTypes = JobType::orderBy('name','ASC')->where('status',1)->get();

        //finding jobId from Job table
        $job = Job::where([
            'user_id' => Auth::user()->id,
            'id'=>$jobId
        ])->first(); 


        if($job == null){
            abort(404);
        }
        

    // Pass the job to the edit view
    return view('front.account.job.edit', [
        'categories' => $categories,
         'jobTypes' => $jobTypes,
         'job' => $job
    ]);
    }

    public function updateJob(Request $request,$id){

        $rules=[
            'title' => 'required|min:5|max:200',
            'category' => 'required',
            'jobType' => 'required',
            'vacancy' => 'required|integer|min:1',
            'location' => 'required|max:50',
            'description' => 'required',
            'company_name' => 'required|min:4|max:50',
        ];
        $validator = Validator::make($request->all(),$rules);

        if($validator->passes()){
           
         $job =Job::find($id);
         $job->title = $request->title;
         $job->category_id = $request->category;
         $job->job_type_id = $request->jobType;
         $job->user_id = Auth::user()->id;
         $job->vacancy = $request->vacancy;
         $job->salary = $request->salary;
         $job->location = $request->location;
         $job->description = $request->description;
         $job->benefits = $request->benefits;
         $job->responsibility = $request->responsibility;
         $job->qualifications = $request->qualifications;
         $job->keywords= $request->keywords;
         $job->experience = $request->experience;
         $job->company_name = $request->company_name;
         $job->company_location = $request->company_location;
         $job->company_website = $request->company_website;
         $job->save();

          
        session()->flash('success','Job Updated successfully');

         return response()->json([
            'status' => true,
            'errors' =>[]
        ]);


        }


        else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    // deleteJob

    public function deleteJob(Request $request){

       $job = Job::where([
            'user_id' => Auth::user()->id,
            'id' => $request->jobId
        ])->first();

        if($job == null){
            session()->flash('error','Job with this id not exists');

            return response()->json([
                'status' => true
            ]);
        }

        Job::where('id',$request->jobId)->delete();
        session()->flash('success','Job deleted Successfully!');

        return response()->json([
            'status' => true
        ]);
    }

    // AppledJobs
    public function myJobApplications(){
        $jobApplications=JobApplication::where('user_id',Auth::user()->id)
        ->with(['job','job.jobType','job.applications'])
        ->paginate(10);

        return view('front.account.job.my-job-applications',[
            'jobApplications' => $jobApplications
        ]);

    }

    public function reomoveJobs(Request $request){
        $jobApplication = JobApplication::where([
                                        'id' => $request->id,
                                        'user_id' => Auth::user()->id]
                                    )->first();
  

      if($jobApplication == null){
        session()->flash('error','No such job application exists');
        return response()->json([
            'status' => false,
        ]);
      }

      JobApplication::find($request->id)->delete();

      session()->flash('success','Job application removed Successfully');
      return response()->json([
          'status' => true,
      ]);

                                        

    }

    // SavedJob page
    public function savedJobs(){
        // $jobApplications=JobApplication::where('user_id',Auth::user()->id)
        // ->with(['job','job.jobType','job.applications'])
        // ->paginate(10);


        $savedJobs=SavedJob::where([
            'user_id' => Auth::user()->id
        ])->with(['job','job.jobType','job.applications'])->orderBy('created_at','DESC')->paginate(10);
        
        return view('front.account.job.saved-jobs',[
            'savedJobs' => $savedJobs
        ]);
    }
     
    // remove Save Job
    public function removeSavedJob(Request $request){
        $savedJob = SavedJob::where([
                                        'id' => $request->id,
                                        'user_id' => Auth::user()->id]
                                    )->first();
  

      if($savedJob == null){
        session()->flash('error','No such job application exists');
        return response()->json([
            'status' => false,
        ]);
      }

      SavedJob::find($request->id)->delete();

      session()->flash('success','Job application unsaved Successfully');
      return response()->json([
          'status' => true,
      ]);

                                        

    }

    // updatePassword
    public function updatePassword(Request $request){
      $validator = Validator::make($request->all(),[
        'old_password' => 'required',
        'new_password' => 'required|min:6',
        'confirm_password' => 'required|same:new_password',
      ]);
     
      if($validator->fails()){
        return response()->json([
            'status' => false,
            'errors' => $validator->errors(),
        ]);
      }

    //   matching oldpassword
    if(Hash::check($request->old_password , Auth::user()->password) === false){
        session()->flash('error','Your Old Password is Incorrect');
        return response()->json([
            'status' => false,
            
        ]);
    }

    $user = User::find(Auth::user()->id);
    $user->password = Hash::make($request->new_password); //updating new Password
    $user->save();

    session()->flash('success','Password Updated Successfully!');
    return response()->json([
        'status' => true,
        
    ]);

    }
}
