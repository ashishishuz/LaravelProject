<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Job;



class HomeController extends Controller
{
    //this function will show home page
    public function index(){

       $categories = Category::where('status',1)->orderBy('name','ASC')->take(8)->get();

       $newCategories = Category::where('status',1)->orderBy('name','ASC')->get();

       $featuredJobs = Job::where('status',1)
       ->orderBy('created_at','DESC')
       ->with('jobType')
       ->where('isFeatured',1)->take(6)->get();


       $latestjobs = Job::where('status',1)
       ->with('jobType')
       ->orderBy('created_at','DESC')
       ->take(6)->get();


        return view('front.home',[
            'categories' => $categories,
            'featuredJobs' => $featuredJobs,
            'latestjobs' => $latestjobs,
            'newCategories' => $newCategories
        ]);
        
    }
// yt 12:31
 
}
