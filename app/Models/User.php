<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = ['user_id', 'name', 'email', 'mobile', 'pwd', 'status'];
    protected $hidden = ['pwd'];

    public function address()
    {
        return $this->hasOne(Address::class);
    }
}
