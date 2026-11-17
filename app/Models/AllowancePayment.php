<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllowancePayment extends Model
{
    protected $fillable = ['due_on', 'transaction_id'];

    protected function casts(): array
    {
        return ['due_on' => 'date'];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(AllowanceSchedule::class, 'allowance_schedule_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}