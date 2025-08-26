<?php

namespace App\Models;

use App\Traits\HasResourceAuthorization;
use App\Traits\HasRoleAuthorization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasResourceAuthorization, HasRoleAuthorization {
        HasRoleAuthorization::canViewKid insteadof HasResourceAuthorization;
        HasRoleAuthorization::canEditKid insteadof HasResourceAuthorization;
        HasRoleAuthorization::getAccessibleKidsQuery insteadof HasResourceAuthorization;
        HasResourceAuthorization::canViewKid as canViewKidByPermission;
        HasResourceAuthorization::canEditKid as canEditKidByPermission;
        HasResourceAuthorization::getAccessibleKidsQuery as getAccessibleKidsQueryByPermission;
    }
    use HasRoles;
    use Notifiable;
    use SoftDeletes;

    public $temporaryPassword;
    public $temporary_password; // Para uso no Observer

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'allow',
        'created_by',
        'updated_by',
        'deleted_by',
        'phone',
        'postal_code',
        'street',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'provider_id',
        'provider_email',
        'provider_avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'allow' => 'boolean',
    ];

    public const SUPERADMIN = 1;

    public const ADMIN = 2;

    public const ROLE_PAIS = 3;

    public const ROLE_PROFESSION = 4;

    // Constantes para os tipos
    public const TYPE_I = 'i';

    public const TYPE_E = 'e';

    public const TYPE = [
        'i' => 'Interno',
        'e' => 'Externo',
    ];

    public function kids()
    {
        return $this->hasMany(Kid::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function professionals()
    {
        return $this->belongsToMany(Professional::class, 'user_professional');
    }

    // === REGRAS DE NEGÓCIO ===
    
    public function isActive(): bool
    {
        return (bool) $this->allow;
    }

    public function canBeDeleted(): bool
    {
        return empty($this->isDeletionAllowed());
    }

    public function isDeletionAllowed(): array
    {
        $errors = [];
        
        if (auth()->id() === $this->id) {
            $errors[] = 'Não é possível excluir seu próprio usuário';
        }

        if ($this->roles()->exists()) {
            $errors[] = 'Não é possível excluir usuário com perfis atribuídos';
        }

        return $errors;
    }

    public function hasTemporaryPassword(): bool
    {
        return !$this->password_changed_at || 
               now()->diffInDays($this->password_changed_at) > 90;
    }

    public function isValidUser(): bool
    {
        return $this->isActive() && 
               $this->email_verified_at !== null && 
               !$this->deleted_at;
    }

    public function needsPasswordChange(): bool
    {
        return $this->hasTemporaryPassword();
    }

    public function markPasswordAsChanged(): void
    {
        $this->update(['password_changed_at' => now()]);
    }

    public function hasProfessionalRole(): bool
    {
        return $this->can('attach-to-kids-as-professional');
    }

    public function sanitizeData(array $data): array
    {
        return [
            'name' => strip_tags($data['name']),
            'email' => filter_var($data['email'], FILTER_SANITIZE_EMAIL),
            'phone' => isset($data['phone']) ? preg_replace('/[^0-9()\\s-]/', '', $data['phone']) : null,
            'postal_code' => isset($data['cep']) ? preg_replace('/[^0-9-]/', '', $data['cep']) : null,
            'street' => isset($data['logradouro']) ? strip_tags($data['logradouro']) : null,
            'number' => isset($data['numero']) ? strip_tags($data['numero']) : null,
            'complement' => isset($data['complemento']) ? strip_tags($data['complemento']) : null,
            'neighborhood' => isset($data['bairro']) ? strip_tags($data['bairro']) : null,
            'city' => isset($data['cidade']) ? strip_tags($data['cidade']) : null,
            'state' => isset($data['estado']) ? strtoupper(strip_tags($data['estado'])) : null,
            'allow' => (bool) ($data['allow'] ?? true), // PADRÃO: usuário ativo
            'type' => $data['type'] ?? self::TYPE_I,
        ];
    }

    public function getStatusBadgeClass(): string
    {
        return $this->isActive() ? 'bg-success' : 'bg-danger';
    }

    public function getStatusText(): string
    {
        return $this->isActive() ? 'Ativo' : 'Inativo';
    }

    public function getDisplayName(): string
    {
        return $this->name;
    }

    public function getInitials(): string
    {
        return strtoupper(substr($this->name, 0, 1));
    }

    public function hasCompleteProfile(): bool
    {
        return !empty($this->name) && 
               !empty($this->email) && 
               $this->email_verified_at !== null;
    }


    public function scopeGetUsers()
    {
        if (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()) {
            return self::with('role')->where([
                ['role_id', '!=', 1],
            ])->orWhere('created_by', '=', auth()->user()->id);
        } else {
            return self::where('created_by', '=', auth()->user()->id);
        }
    }

    public static function scopeListUsers()
    {
        return self::where([
            ['role_id', '!=', 1],
            ['role_id', '!=', 2],
            ['role_id', '!=', 3],
        ])->get();
    }

    public static function listAssocUser($type)
    {
        if (auth()->user()->isSuperAdmin()) {
            return self::where('type', '=', $type)->get();
        } elseif (auth()->user()->isAdmin()) {
            return self::where('type', '=', $type)
                ->where('created_by', '!=', 1)->get();
        } else {
            return self::where('type', '=', $type)
                ->where('created_by', '=', auth()->user()->id)->get();
        }
    }

    public function professional()
    {
        return $this->belongsToMany(Professional::class, 'user_professional');
    }

    public function getSpecialtyAttribute()
    {
        return $this->professional->first()?->specialty;
    }
}
