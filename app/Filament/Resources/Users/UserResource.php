<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use App\Services\ActivityLogger;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Gouvernance';

    protected static ?string $navigationLabel = 'Utilisateurs';

    protected static ?string $modelLabel = 'utilisateur';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Compte')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Nom')
                                ->required()
                                ->maxLength(180),
                            TextInput::make('email')
                                ->required()
                                ->email()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),
                            TextInput::make('password')
                                ->label('Mot de passe')
                                ->password()
                                ->revealable()
                                ->minLength(12)
                                ->required(fn (string $operation): bool => $operation === 'create')
                                ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                                ->dehydrated(fn (?string $state): bool => filled($state))
                                ->helperText('Laisser vide pour conserver le mot de passe actuel.'),
                            Select::make('roles')
                                ->label('Rôles')
                                ->relationship(
                                    'roles',
                                    'name',
                                    modifyQueryUsing: fn (Builder $query): Builder => auth()->user()?->hasRole('superadmin')
                                        ? $query
                                        : $query->where('name', '!=', 'superadmin'),
                                )
                                ->multiple()
                                ->maxItems(1)
                                ->default(fn (): array => array_filter([Role::query()->where('name', 'manager')->value('id')]))
                                ->preload()
                                ->searchable()
                                ->disabled(fn (?User $record): bool => ! (auth()->user()?->can('manage roles') ?? false)
                                    || ($record?->hasRole('superadmin') && User::role('superadmin')->count() <= 1))
                                ->saveRelationshipsUsing(function (Select $component): void {
                                    $actor = auth()->user();
                                    abort_unless($actor?->can('manage roles'), 403);

                                    /** @var User $record */
                                    $record = $component->getRecord();
                                    $roleIds = collect($component->getState() ?? [])->map(fn (mixed $id): int => (int) $id);
                                    $roles = Role::query()->whereKey($roleIds)->get();

                                    abort_if($roles->contains('name', 'superadmin') && ! $actor->hasRole('superadmin'), 403);
                                    abort_if(
                                        $record->hasRole('superadmin')
                                        && User::role('superadmin')->count() <= 1
                                        && ! $roles->contains('name', 'superadmin'),
                                        422,
                                        'Le dernier super administrateur doit conserver son rôle.',
                                    );

                                    $record->syncRoles($roles);
                                    app(ActivityLogger::class)->record(
                                        'user.roles_updated',
                                        $record,
                                        $actor->id,
                                        ['role_count' => $roles->count()],
                                    );
                                })
                                ->required(),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->label('Rôles')
                    ->badge()
                    ->separator(', '),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label('Rôle')
                    ->relationship('roles', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUsers::route('/'),
        ];
    }
}
