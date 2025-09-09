<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use App\Models\Registration;
use Illuminate\Http\Request;
use App\Application\Queries\GetUserEventsQuery;
use App\Application\Handlers\GetUserEventsHandler;
use App\Infrastructure\Persistence\EloquentEventRepository;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    private $eventRepository;
    private $getUserEventsHandler;

    public function __construct(
        EloquentEventRepository $eventRepository,
        GetUserEventsHandler $getUserEventsHandler
    ) {
        $this->eventRepository = $eventRepository;
        $this->getUserEventsHandler = $getUserEventsHandler;
    }

    function IndexPage(): View
    {
        try {
            // Get featured events using DDD repository
            $featurEvents = $this->eventRepository->getEventsByType('Feature', 4);
            
            // Get recent events using DDD repository with pagination
            $recentEvents = $this->eventRepository->getEventsByTypeWithPagination('Recent', 6);
            
            return view('frontend.pages.index-page', compact('featurEvents', 'recentEvents'));
        } catch (\Exception $e) {
            Log::error('Error in IndexPage: ' . $e->getMessage());
            
            // Return view with empty arrays if database fails
            $featurEvents = [];
            $recentEvents = [];
            
            return view('frontend.pages.index-page', compact('featurEvents', 'recentEvents'))
                ->with('error', 'Some events may not be available at the moment. Please try again later.');
        }
    }

    public function PostPage($id)
    {
        try {
            // Load event using DDD repository
            $post = $this->eventRepository->findByIdWithRelations($id);
            
            if (!$post) {
                abort(404, 'Event not found');
            }
            
            // Get related events using DDD repository
            $relatedEvents = $this->eventRepository->getRelatedEvents(
                $post->getCategoryId(), 
                $id, 
                3
            );
                
            return view('frontend.pages.post-page', compact('post', 'relatedEvents'));
        } catch (\Exception $e) {
            Log::error('Error in PostPage: ' . $e->getMessage());
            
            // If database error, show error page or redirect
            return redirect()->route('home')->with('error', 'Unable to load event details. Please try again later.');
        }
    }
    
    function EventRegistration(Request $request)
    {
        try {
            Registration::create([
                'date' => now()->toDateString(),
                'name' => $request->input('name'),
                'mobile' => $request->input('mobile'),
                'email' => $request->input('email'),
                'remark' => $request->input('remark'),
                'event_id' => $request->input('event_id'),
                'user_id' => $request->input('user_id')
            ]);

            return redirect()->back()->with('success', 'Your Registration Confirmed Successfully!');
        } catch (\Exception $e) {
            Log::error('Error in EventRegistration: ' . $e->getMessage());
            
            return redirect()->back()->with('error', 'Registration failed. Please try again later.');
        }
    }
}
