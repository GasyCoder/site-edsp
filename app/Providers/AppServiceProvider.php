<?php

namespace App\Providers;

use App\Models\AcademicLevel;
use App\Models\ActivityLog;
use App\Models\AdmissionCampaign;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\ContactMessage;
use App\Models\ContentRevision;
use App\Models\CourseElement;
use App\Models\Department;
use App\Models\Document;
use App\Models\ExamSession;
use App\Models\Gallery;
use App\Models\Media;
use App\Models\Mention;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Parcours;
use App\Models\ParcoursLevel;
use App\Models\Partner;
use App\Models\Program;
use App\Models\Redirect;
use App\Models\Semester;
use App\Models\Setting;
use App\Models\TeachingUnit;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\User;
use App\Observers\ActivityLifecycleObserver;
use App\Observers\ContentLifecycleObserver;
use App\Observers\ContentSlugObserver;
use App\Policies\AcademicPolicy;
use App\Policies\ActivityLogPolicy;
use App\Policies\AdmissionCampaignPolicy;
use App\Policies\ApplicationDocumentPolicy;
use App\Policies\ApplicationPolicy;
use App\Policies\ContactMessagePolicy;
use App\Policies\ContentRevisionPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\GalleryPolicy;
use App\Policies\MediaPolicy;
use App\Policies\NewsCategoryPolicy;
use App\Policies\NewsPolicy;
use App\Policies\PagePolicy;
use App\Policies\PageSectionPolicy;
use App\Policies\PartnerPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\ProgramPolicy;
use App\Policies\RedirectPolicy;
use App\Policies\RolePolicy;
use App\Policies\SettingPolicy;
use App\Policies\TeamMemberPolicy;
use App\Policies\TestimonialPolicy;
use App\Policies\UserPolicy;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Events\RoleDetachedEvent;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        EditAction::configureUsing(fn (EditAction $action): EditAction => $action->iconButton()->tooltip('Modifier'));
        DeleteAction::configureUsing(fn (DeleteAction $action): DeleteAction => $action->iconButton()->tooltip('Supprimer'));
        ViewAction::configureUsing(fn (ViewAction $action): ViewAction => $action->iconButton()->tooltip('Consulter'));
        RestoreAction::configureUsing(fn (RestoreAction $action): RestoreAction => $action->iconButton()->tooltip('Restaurer'));
        ForceDeleteAction::configureUsing(fn (ForceDeleteAction $action): ForceDeleteAction => $action->iconButton()->tooltip('Supprimer définitivement'));

        config()->set('permission.events_enabled', true);
        Event::listen(RoleDetachedEvent::class, function (RoleDetachedEvent $event): void {
            if (! $event->model instanceof User) {
                return;
            }

            $superAdminRole = Role::query()->where('name', 'superadmin')->where('guard_name', 'web')->first();
            $detachedRoleIds = collect($event->rolesOrIds)->map(fn ($role) => $role instanceof Role ? $role->getKey() : $role);

            if ($superAdminRole && $detachedRoleIds->contains($superAdminRole->getKey()) && User::role($superAdminRole)->doesntExist()) {
                $event->model->assignRole($superAdminRole);
            }
        });

        Gate::before(function (User $user, string $ability, mixed ...$arguments): ?bool {
            if (! $user->hasRole('superadmin')) {
                return null;
            }

            $subject = $arguments[0] ?? null;
            $userInvariant = in_array($ability, ['delete', 'deleteAny'], true)
                && ($subject instanceof User || $subject === User::class);
            $roleInvariant = in_array($ability, ['update', 'delete'], true)
                && $subject instanceof Role
                && $subject->name === 'superadmin';

            return ($userInvariant || $roleInvariant) ? null : true;
        });

        Gate::policy(Page::class, PagePolicy::class);
        Gate::policy(PageSection::class, PageSectionPolicy::class);
        Gate::policy(News::class, NewsPolicy::class);
        Gate::policy(NewsCategory::class, NewsCategoryPolicy::class);
        Gate::policy(Program::class, ProgramPolicy::class);
        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(TeamMember::class, TeamMemberPolicy::class);
        Gate::policy(Gallery::class, GalleryPolicy::class);
        Gate::policy(Document::class, DocumentPolicy::class);
        Gate::policy(Partner::class, PartnerPolicy::class);
        Gate::policy(Testimonial::class, TestimonialPolicy::class);
        Gate::policy(AdmissionCampaign::class, AdmissionCampaignPolicy::class);
        Gate::policy(Application::class, ApplicationPolicy::class);
        Gate::policy(ApplicationDocument::class, ApplicationDocumentPolicy::class);
        Gate::policy(ContactMessage::class, ContactMessagePolicy::class);
        Gate::policy(Media::class, MediaPolicy::class);
        Gate::policy(Setting::class, SettingPolicy::class);
        Gate::policy(ContentRevision::class, ContentRevisionPolicy::class);
        Gate::policy(ActivityLog::class, ActivityLogPolicy::class);
        Gate::policy(Redirect::class, RedirectPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
        foreach ([
            AcademicLevel::class,
            CourseElement::class,
            ExamSession::class,
            Mention::class,
            Parcours::class,
            ParcoursLevel::class,
            Semester::class,
            TeachingUnit::class,
        ] as $academicModel) {
            Gate::policy($academicModel, AcademicPolicy::class);
        }

        Page::observe(ContentSlugObserver::class);
        News::observe(ContentSlugObserver::class);
        Program::observe(ContentSlugObserver::class);

        foreach ([Page::class, PageSection::class, News::class, Program::class, Gallery::class, AdmissionCampaign::class] as $contentModel) {
            $contentModel::observe(ContentLifecycleObserver::class);
        }

        foreach ([
            ContactMessage::class,
            Department::class,
            Document::class,
            NewsCategory::class,
            Partner::class,
            Redirect::class,
            Setting::class,
            TeamMember::class,
            Testimonial::class,
            User::class,
            Role::class,
            Permission::class,
            AcademicLevel::class,
            CourseElement::class,
            ExamSession::class,
            Mention::class,
            Parcours::class,
            ParcoursLevel::class,
            Semester::class,
            TeachingUnit::class,
        ] as $auditedModel) {
            $auditedModel::observe(ActivityLifecycleObserver::class);
        }

        $this->protectPublicationStatus(Page::class, 'publish pages');
        $this->protectPublicationStatus(News::class, 'publish news');
        $this->protectPublicationStatus(Program::class, 'publish programs');

        Gate::define('access-admin', fn (User $user): bool => $user->can('access admin'));
        Gate::define('edit-site', fn (User $user): bool => $user->can('edit pages'));
    }

    /** @param class-string<Model> $model */
    private function protectPublicationStatus(string $model, string $permission): void
    {
        $model::saving(function (Model $content) use ($permission): void {
            $status = $content->getAttribute('status');
            $status = $status instanceof BackedEnum ? (string) $status->value : (string) $status;

            if ($status === 'published' && blank($content->getAttribute('published_at'))) {
                $content->setAttribute('published_at', now());
            }

            if ($status === 'scheduled' && blank($content->getAttribute('published_at'))) {
                throw ValidationException::withMessages([
                    'published_at' => 'Une date de publication est requise pour un contenu programmé.',
                ]);
            }

            $protectedTransition = $content->exists
                ? $content->isDirty(['status', 'published_at'])
                : ! in_array($status, ['draft', 'pending'], true);

            if ($protectedTransition && auth()->check() && ! auth()->user()->can($permission)) {
                abort(403, 'Vous ne disposez pas de la permission de publication requise.');
            }
        });
    }
}
