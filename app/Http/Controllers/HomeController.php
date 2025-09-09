<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use App\Models\Registration;
use Illuminate\Http\Request;
use App\Application\Queries\GetUserEventsQuery;
use App\Application\Handlers\GetUserEventsHandler;
use App\Infrastructure\Persistence\EloquentEventRepository;

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
        // Get featured events using DDD repository
        $featurEvents = $this->eventRepository->getEventsByType('Feature', 4);
        
        // Get recent events using DDD repository with pagination
        $recentEvents = $this->eventRepository->getEventsByTypeWithPagination('Recent', 6);
        
        return view('frontend.pages.index-page', compact('featurEvents', 'recentEvents'));
    }

    public function PostPage($id)
    {
        // Load event using DDD repository
        $post = $this->eventRepository->findByIdWithRelations($id);
        
        if (!$post) {
            abort(404, 'Event not found');
        }
        
        // Get related events using DDD repository
        $relatedEvents = $this->eventRepository->getRelatedEvents(
            $post->categorie_id, 
            $id, 
            3
        );
            
        return view('frontend.pages.post-page', compact('post', 'relatedEvents'));
    }
    
    function EventRegistration(Request $request)
    {
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
    }
}
