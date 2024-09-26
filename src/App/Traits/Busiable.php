<?php

namespace The3labsTeam\NovaBusyResourceField\App\Traits;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait Busiable
{
    protected $userModel;

    public function __construct()
    {
        // Dynamically get the User model class from the config
        $this->userModel = config('nova-busy-resource.user_model', \App\Models\User::class);
    }

    /**
     * Return the user who is busy with this resource
     */
    public function busyFrom(User $user) // Type-hint remains intact
    {
        $this->busier()->syncWithoutDetaching([$user->id => ['created_at' => now(), 'updated_at' => now()]]);
    }

    public function unbusy()
    {
        return $this->busier()->delete();
    }

    public function isBusy(): bool
    {
        return $this->busier()->count() > 0;
    }

    public function busyData(): array
    {
        return $this->busier()->first()->toArray();
    }

    public function isNotBusy(): bool
    {
        return ! $this->isBusy();
    }

    public function isBusyByUser(User $user): bool // Type-hint remains intact
    {
        return $this->busier()->where('user_id', $user->id)->exists();
    }

    public function scopeWhereBusy($query)
    {
        return $query->whereHas('busier');
    }

    public function scopeWhereNotBusy($query)
    {
        return $query->whereDoesntHave('busier');
    }

    //=== RELATIONSHIPS ===//
    public function busier(): MorphToMany
    {
        // Dynamically resolve the class via the config
        return $this->morphToMany($this->userModel, 'busiable')->withPivot('created_at', 'updated_at');
    }
}
