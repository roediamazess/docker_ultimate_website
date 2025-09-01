<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TablesController extends Controller
{
    public function group()
    {
        // Fetch all groups from database and convert to array
        $groups = \DB::table('hotel_groups')->orderBy('name', 'ASC')->get()->map(function($group) {
            return (array) $group;
        })->toArray();
        
        return view('tables.group', compact('groups'));
    }

    public function storeGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            \DB::table('hotel_groups')->insert([
                'name' => $request->name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return redirect()->back()->with('notification', [
                'type' => 'success',
                'message' => 'Group created successfully.'
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('notification', [
                'type' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function updateGroup(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'name' => 'required|string|max:255',
        ]);

        try {
            \DB::table('hotel_groups')
                ->where('id', $request->id)
                ->update([
                    'name' => $request->name,
                    'updated_at' => now(),
                ]);
            
            return redirect()->back()->with('notification', [
                'type' => 'success',
                'message' => 'Group updated successfully.'
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('notification', [
                'type' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function typography()
    {
        return view('tables.typography');
    }

    public function colors()
    {
        return view('tables.colors');
    }

    public function button()
    {
        return view('tables.button');
    }

    public function dropdown()
    {
        return view('tables.dropdown');
    }

    public function alert()
    {
        return view('tables.alert');
    }

    public function card()
    {
        return view('tables.card');
    }

    public function carousel()
    {
        return view('tables.carousel');
    }

    public function avatar()
    {
        return view('tables.avatar');
    }

    public function progress()
    {
        return view('tables.progress');
    }

    public function tabs()
    {
        return view('tables.tabs');
    }

    public function pagination()
    {
        return view('tables.pagination');
    }

    public function badges()
    {
        return view('tables.badges');
    }
}
