<?php

use App\Http\Controllers\Api\HeroBannerController;
use App\Http\Controllers\Api\NavigationController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\ProductController as ApiProductController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\SiteSettingController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController as PublicPageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\EnsureUserIsRegularUser;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

// Locale switching route
Route::get('locale/{locale}', [\App\Http\Controllers\LocaleController::class, 'switch'])->name('locale.switch');

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('products', [ProductController::class, 'index'])->name('products.index');
Route::get('products/{slug}', [ProductController::class, 'show'])->name('products.show');

Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('categories/{slug}', [CategoryController::class, 'show'])->name('categories.show');

// Promotions routes
Route::get('promotions', [\App\Http\Controllers\PromotionController::class, 'index'])->name('promotions.index');

// Content tabs routes
Route::get('news', function () {
    return Inertia::render('News/Index');
})->name('news.index');

Route::get('events', function () {
    return Inertia::render('Events/Index');
})->name('events.index');

Route::get('tips', function () {
    return Inertia::render('Tips/Index');
})->name('tips.index');

Route::get('reviews', function () {
    return Inertia::render('Reviews/Index');
})->name('reviews.index');

// Pages routes
Route::get('pages', [PublicPageController::class, 'index'])->name('pages.index');

// Cart routes
Route::get('cart', function () {
    return Inertia::render('Cart');
})->name('cart.index');
Route::post('cart/add', [CartController::class, 'add'])->name('cart.add');
Route::put('cart/update', [CartController::class, 'update'])->name('cart.update');
Route::delete('cart/remove/{productId}', [CartController::class, 'remove'])->name('cart.remove');
Route::delete('cart/clear', [CartController::class, 'clear'])->name('cart.clear');
Route::get('cart/data', [CartController::class, 'index'])->name('cart.data');

// Order routes
Route::get('checkout', [OrderController::class, 'checkout'])->name('checkout');
Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
Route::get('orders/{id}', [OrderController::class, 'show'])->name('orders.show');

// Profile routes (requires authentication and user role)
Route::middleware(['auth', EnsureUserIsRegularUser::class])->group(function () {
    Route::get('profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('profile/addresses', [ProfileController::class, 'storeAddress'])->name('profile.addresses.store');
    Route::put('profile/addresses/{id}', [ProfileController::class, 'updateAddress'])->name('profile.addresses.update');
    Route::delete('profile/addresses/{id}', [ProfileController::class, 'deleteAddress'])->name('profile.addresses.delete');
    Route::post('profile/addresses/{id}/set-default', [ProfileController::class, 'setDefaultAddress'])->name('profile.addresses.set-default');
    
    // Wishlist routes
    Route::get('wishlist', [\App\Http\Controllers\WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('wishlist', [\App\Http\Controllers\WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('wishlist/{productId}', [\App\Http\Controllers\WishlistController::class, 'destroy'])->name('wishlist.destroy');
    Route::get('wishlist/check/{productId}', [\App\Http\Controllers\WishlistController::class, 'check'])->name('wishlist.check');
    Route::post('wishlist/check-batch', [\App\Http\Controllers\WishlistController::class, 'checkBatch'])->name('wishlist.check-batch');
});

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Navigation API routes
Route::get('api/navigations', [NavigationController::class, 'index'])->name('api.navigations.index');
Route::get('api/navigations/categories', [NavigationController::class, 'categories'])->name('api.navigations.categories');

// Site Settings API routes
Route::get('api/site-settings', [SiteSettingController::class, 'index'])->name('api.site-settings.index');

// Sitemap route
Route::get('sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

// Robots.txt route (dynamic)
Route::get('robots.txt', [\App\Http\Controllers\RobotsController::class, 'index'])->name('robots');

// Products API routes
Route::get('api/products', [ApiProductController::class, 'index'])->name('api.products.index');

// Pages API routes
Route::get('api/pages', [PageController::class, 'index'])->name('api.pages.index');
Route::post('api/pages', [PageController::class, 'store'])->name('api.pages.store');
Route::get('api/pages/{page}', [PageController::class, 'show'])->name('api.pages.show');
Route::get('api/pages/slug/{slug}', [PageController::class, 'showBySlug'])->name('api.pages.show-by-slug');
Route::put('api/pages/{page}', [PageController::class, 'update'])->name('api.pages.update');
Route::delete('api/pages/{page}', [PageController::class, 'destroy'])->name('api.pages.destroy');

// Promotions API routes
Route::get('api/promotions', [PromotionController::class, 'index'])->name('api.promotions.index');
Route::post('api/promotions', [PromotionController::class, 'store'])->name('api.promotions.store');
Route::get('api/promotions/{promotion}', [PromotionController::class, 'show'])->name('api.promotions.show');
Route::put('api/promotions/{promotion}', [PromotionController::class, 'update'])->name('api.promotions.update');
Route::delete('api/promotions/{promotion}', [PromotionController::class, 'destroy'])->name('api.promotions.destroy');

// Hero Banners API routes
Route::get('api/hero-banners', [HeroBannerController::class, 'index'])->name('api.hero-banners.index');
Route::post('api/hero-banners', [HeroBannerController::class, 'store'])->name('api.hero-banners.store');
Route::get('api/hero-banners/{heroBanner}', [HeroBannerController::class, 'show'])->name('api.hero-banners.show');
Route::put('api/hero-banners/{heroBanner}', [HeroBannerController::class, 'update'])->name('api.hero-banners.update');
Route::delete('api/hero-banners/{heroBanner}', [HeroBannerController::class, 'destroy'])->name('api.hero-banners.destroy');

// Product Category Showcases API routes
Route::get('api/product-category-showcases', [\App\Http\Controllers\Api\ProductCategoryShowcaseController::class, 'index'])->name('api.product-category-showcases.index');

// Newsletter API routes
Route::post('api/newsletter', [\App\Http\Controllers\Api\NewsletterController::class, 'store'])->name('api.newsletter.store');

// Delivery Methods API routes
Route::get('api/delivery-methods', [\App\Http\Controllers\Api\DeliveryMethodController::class, 'index'])->name('api.delivery-methods.index');
Route::get('api/delivery-methods/{deliveryMethod}', [\App\Http\Controllers\Api\DeliveryMethodController::class, 'show'])->name('api.delivery-methods.show');

// Adminer route (protected by admin middleware)
Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsAdmin::class])
    ->prefix('adminer')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\AdminerController::class, 'index'])->name('adminer.index');
    });

// Settings routes (must be before catch-all route)
require __DIR__.'/settings.php';

// Page slug route (must be last to avoid conflicts with other routes)
Route::get('{slug}', [PublicPageController::class, 'show'])->name('pages.show');
