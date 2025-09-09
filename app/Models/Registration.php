<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Domain\Event\Entities\Event as EventEntity;

class Registration extends Model
{
    protected $fillable = ['date', 'name', 'mobile', 'email', 'remark', 'event_id', 'user_id'];
    
    /**
     * Get the event that this registration belongs to
     * Note: This returns the raw database data, not the DDD Entity
     * For DDD operations, use the EventRepository directly
     */
    function event(): BelongsTo
    {
        // This creates a temporary Eloquent model for the relationship
        // The actual business logic should use the DDD Event Entity
        return $this->belongsTo(EventEloquentModel::class, 'event_id');
    }
    
    /**
     * Get the DDD Event Entity for this registration
     * This is the preferred way to access event data in DDD context
     */
    public function getEventEntity(): ?EventEntity
    {
        $eventRepository = app(\App\Infrastructure\Persistence\EloquentEventRepository::class);
        return $eventRepository->findById($this->event_id);
    }
}

/**
 * Temporary Eloquent model for Registration relationship
 * This maintains backward compatibility while we transition to full DDD
 */
class EventEloquentModel extends Model
{
    protected $table = 'events';
    protected $fillable = [
        'title', 'description', 'date', 'time', 'location', 
        'type', 'image', 'user_id', 'categorie_id'
    ];
    
    // Add relationships that Registration might need
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
    
    public function category()
    {
        return $this->belongsTo(\App\Models\Category::class, 'categorie_id');
    }
}
