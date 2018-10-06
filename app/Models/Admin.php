<?php

namespace Caronae\Models;

use Backpack\Base\app\Notifications\ResetPasswordNotification as ResetPasswordNotification;
use Backpack\CRUD\CrudTrait;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;

class Admin extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, Notifiable;
    use CrudTrait;

    protected $table = 'admins';
    protected $fillable = ['name', 'email', 'password', 'user_id'];
    protected $hidden = ['password', 'remember_token', 'updated_at', 'created_at'];

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
