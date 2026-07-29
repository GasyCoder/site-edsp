<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ApplicationDocumentController;
use App\Http\Controllers\ApplicationStatusController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContentRevisionController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\NewsletterCampaignAttachmentController;
use App\Http\Controllers\NewsletterSubscriptionController;
use App\Http\Controllers\PageSectionController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SitemapController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicSiteController::class, 'home'])->name('home');
Route::post('/langue/{locale}', LocaleController::class)
    ->whereIn('locale', ['fr', 'en'])
    ->middleware('throttle:30,1')
    ->name('locale.update');
Route::get('/formations', [PublicSiteController::class, 'programs'])->name('programs.index');
Route::get('/formations/{program:slug}', [PublicSiteController::class, 'program'])
    ->missing(fn (Request $request) => app(RedirectController::class)($request))
    ->name('programs.show');
Route::get('/actualites', [PublicSiteController::class, 'news'])->name('news.index');
Route::get('/actualites/{news:slug}', [PublicSiteController::class, 'article'])
    ->missing(fn (Request $request) => app(RedirectController::class)($request))
    ->name('news.show');
Route::get('/preinscription', fn () => redirect('/inscription', 301));
Route::post('/preinscription', [ApplicationController::class, 'store'])->middleware('throttle:5,60');
Route::get('/inscription', [ApplicationController::class, 'create'])->name('applications.create');
Route::post('/inscription', [ApplicationController::class, 'store'])->middleware('throttle:5,60')->name('applications.store');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,10')->name('contact.store');
Route::post('/newsletter', [NewsletterSubscriptionController::class, 'store'])->middleware('throttle:5,10')->name('newsletter.store');
Route::get('/newsletter/confirmer/{subscriber}', [NewsletterSubscriptionController::class, 'verify'])->middleware('throttle:20,1')->name('newsletter.verify');
Route::get('/newsletter/desinscription/{subscriber}', [NewsletterSubscriptionController::class, 'unsubscribe'])->middleware('throttle:20,1')->name('newsletter.unsubscribe');
Route::get('/documents', [PublicSiteController::class, 'documents'])->name('documents.index');
Route::get('/documents/{document}/consulter', [DocumentController::class, 'preview'])->middleware('throttle:60,1')->name('documents.preview');
Route::get('/documents/{document}/telecharger', [DocumentController::class, 'download'])->middleware('throttle:60,1')->name('documents.download');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', RobotsController::class)->name('robots');

Route::middleware('auth')->group(function (): void {
    Route::get('/administration/newsletters/{campaign}/piece-jointe', [NewsletterCampaignAttachmentController::class, 'download'])
        ->name('newsletter-campaigns.attachment.download');
    // POST is intentionally accepted for the visual editor: some production
    // proxies and shared hosts reject non-standard form methods such as PATCH.
    Route::match(['post', 'patch'], '/edition/sections/{section}', [PageSectionController::class, 'update'])
        ->name('sections.update');
    Route::match(['post', 'patch'], '/edition/reference-ministerielle', [SettingController::class, 'updateInstitutionalReference'])
        ->name('settings.institutional-reference.update');
    Route::patch('/administration/candidatures/{application}/statut', [ApplicationStatusController::class, 'update'])->name('applications.status.update');
    Route::get('/administration/documents-candidature/{document}/apercu', [ApplicationDocumentController::class, 'preview'])
        ->middleware('throttle:120,1')
        ->name('application-documents.preview');
    Route::get('/administration/documents-candidature/{document}/telecharger', [ApplicationDocumentController::class, 'download'])->name('application-documents.download');
    Route::post('/administration/revisions/{revision}/restaurer', [ContentRevisionController::class, 'restore'])->name('revisions.restore');
    Route::patch('/administration/parametres/{setting}', [SettingController::class, 'update'])->name('settings.update');
    Route::get('/administration/medias', [MediaController::class, 'index'])->name('media.index');
    Route::post('/administration/medias', [MediaController::class, 'store'])->middleware('throttle:30,1')->name('media.store');
    Route::patch('/administration/medias/{media}', [MediaController::class, 'update'])->name('media.update');
    Route::delete('/administration/medias/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
});

Route::get('/{slug}', [PublicSiteController::class, 'page'])
    ->where('slug', '[A-Za-z0-9][A-Za-z0-9-]*')
    ->name('pages.show');

Route::fallback(RedirectController::class);
