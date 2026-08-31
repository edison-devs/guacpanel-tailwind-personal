<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use OwenIt\Auditing\Contracts\Auditable;

class Customer extends Model implements Auditable
{
    use HasFactory;
    use HasUlids;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function scopeFilter($query, Request $request)
    {
        $query
        ->when($request->filled('name'), function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->name . '%');
        })

        ->when($request->filled('email'), function ($q) use ($request) {
            $q->where('email', 'like', '%' . $request->email . '%');
        })

        ->when($request->filled('phone'), function ($q) use ($request) {
            $q->where('phone', 'like', '%' . $request->phone . '%');
        })

        ->when($request->filled('company'), function ($q) use ($request) {
            $q->where('company', 'like', '%' . $request->company . '%');
        })

        ->when($request->filled('date_from'), function ($q) use ($request) {
            $q->whereDate('created_at', '>=', $request->date_from);
        })

        ->when($request->filled('date_to'), function ($q) use ($request) {
            $q->whereDate('created_at', '<=', $request->date_to);
        })

        ->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;
            $q->where(function ($sub) use ($search) {
                $sub->where('name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%');
            });
        })

        ->when($request->filled('status'), function ($q) use ($request) {
            $q->where('is_active', $request->status === 'active' ? 1 : 0);
        });

        return $query;
    }
}
