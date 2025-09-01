<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // Get real statistics from database
            $user_count = DB::table('users')->count();
            $customer_count = DB::table('customers')->count();
            $project_count = DB::table('projects')->count();
            $activity_count = DB::table('activities')->count();
            
            // Get recent activities
            $recent_activities = DB::table('activities')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            
            // Get recent users
            $recent_users = DB::table('users')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            
            return view('dashboard.index', compact(
                'user_count',
                'customer_count', 
                'project_count',
                'activity_count',
                'recent_activities',
                'recent_users'
            ));
            
        } catch (\Exception $e) {
            // If tables don't exist yet, show with default values
            return view('dashboard.index', [
                'user_count' => 0,
                'customer_count' => 0,
                'project_count' => 0,
                'activity_count' => 0,
                'recent_activities' => collect(),
                'recent_users' => collect()
            ]);
        }
    }

    public function crm()
    {
        return view('dashboard.crm');
    }

    public function ecommerce()
    {
        return view('dashboard.ecommerce');
    }

    public function cryptocurrency()
    {
        return view('dashboard.cryptocurrency');
    }

    public function investment()
    {
        return view('dashboard.investment');
    }

    public function lms()
    {
        return view('dashboard.lms');
    }

    public function nftGaming()
    {
        return view('dashboard.nft-gaming');
    }

    public function medical()
    {
        return view('dashboard.medical');
    }

    public function analytics()
    {
        return view('dashboard.analytics');
    }

    public function posInventory()
    {
        return view('dashboard.pos-inventory');
    }
}