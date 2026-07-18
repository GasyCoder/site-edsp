<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Resources\Roles\Pages\ManageRoles;
use App\Services\ActivityLogger;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use UnitEnum;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Gouvernance';

    protected static ?string $navigationLabel = 'Rôles et permissions';

    protected static ?string $modelLabel = 'rôle';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Rôle')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->disabled()
                            ->dehydrated(false)
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(100),
                        Hidden::make('guard_name')
                            ->default('web'),
                        Select::make('permissions')
                            ->label('Permissions')
                            ->relationship('permissions', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->saveRelationshipsUsing(function (Select $component): void {
                                abort_unless(auth()->user()?->can('manage permissions'), 403);

                                /** @var Role $role */
                                $role = $component->getRecord();
                                $permissions = Permission::query()->whereKey($component->getState() ?? [])->get();
                                $role->syncPermissions($permissions);
                                app(PermissionRegistrar::class)->forgetCachedPermissions();
                                app(ActivityLogger::class)->record(
                                    'role.permissions_updated',
                                    $role,
                                    auth()->id(),
                                    ['permission_count' => $permissions->count()],
                                );
                            })
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Rôle')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('permissions_count')
                    ->label('Permissions')
                    ->counts('permissions'),
                TextColumn::make('users_count')
                    ->label('Utilisateurs')
                    ->counts('users'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRoles::route('/'),
        ];
    }
}
