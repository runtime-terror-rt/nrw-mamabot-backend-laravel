<?php

use App\Http\Controllers\AnalyticsSetting\AnalyticsSettingController;
use App\Http\Controllers\Authentication\ForgotPasswordController;
use App\Http\Controllers\Authentication\LoginController;
use App\Http\Controllers\Authentication\SignUpController;
use App\Http\Controllers\Backend\AboutUsController;
use App\Http\Controllers\Subscription\UserSubscriptionController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\Frontend\HeroController;
use App\Http\Controllers\Frontend\MamabotSupportController;
use App\Http\Controllers\Frontend\WebSettingController;
use App\Http\Controllers\Backend\ArticleController;
use App\Http\Controllers\Backend\CommunityGroupsController;
use App\Http\Controllers\Backend\CommunityInteractionController;
use App\Http\Controllers\Backend\CommunityPostController;
use App\Http\Controllers\Backend\OurJourneyController;
use App\Http\Controllers\Backend\OurMissionController;
use App\Http\Controllers\Backend\PageController;
use App\Http\Controllers\Backend\SavedItemsController;
use App\Http\Controllers\Backend\ServiceController;
use App\Http\Controllers\Backend\SubscriptionPlanController;
use App\Http\Controllers\Backend\TeamController;
use App\Http\Controllers\Backend\TestimonialController;
use App\Http\Controllers\Recovery\RecoveryLogController;
use App\Http\Controllers\Recovery\PelvicExerciseLogController;
use App\Http\Controllers\Recovery\PainMovementLogController;
use App\Http\Controllers\Recovery\FeedingLogController;
use App\Http\Controllers\Recovery\DiaperLogController;
use App\Http\Controllers\Recovery\SleepTrackingController;
use App\Http\Controllers\Recovery\IncisionHealingCheckController;
use App\Http\Controllers\Recovery\MovementRestrictionController;
use App\Http\Controllers\Recovery\BabyMovementLogController;
use App\Http\Controllers\Recovery\HydrationLogController;
use App\Http\Controllers\Frontend\ContactController;
use App\Http\Controllers\Frontend\NewsletterController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Settings\NotificationSettingController;
use App\Http\Controllers\Settings\PersonalizedController;
use App\Http\Controllers\Settings\PrivacyDataController;
use App\Http\Controllers\Settings\SmartPersonalizedController;
use App\Jobs\SendOtpEmail;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Recovery\MotherWellnessLogController;
use App\Http\Controllers\Recovery\MentalHealthLogController;
use App\Http\Controllers\Recovery\NutritionLogController;
use App\Http\Controllers\Recovery\BabyCueLogController;

use App\Http\Controllers\User\UserController;

use App\Http\Controllers\AI\PregnancyFoodController;
use App\Http\Controllers\AI\PregnancyFoodWeeklyLogController;
use App\Http\Controllers\AI\PregnancyFoodRecipeController;
use App\Http\Controllers\AI\PregnancyFoodMealPlanController;
use App\Http\Controllers\AI\PregnancyProductController;
use App\Http\Controllers\AI\DailyInsightController;
use App\Http\Controllers\AI\PostpartumDailyTipController;

use App\Http\Controllers\Analytics\AnalyticsController;

use App\Http\Controllers\AI\AiChatLogController;

use App\Http\Controllers\DoctorQA\DoctorController;
use App\Http\Controllers\DoctorQA\QaController;
use App\Http\Controllers\DoctorQA\QaSessionController;
use App\Http\Controllers\Frontend\FaqController;
use App\Http\Controllers\RelaxationAudio\RelaxationAudioController;
use App\Http\Controllers\WekknessSelfCare\WellnessActivityController;

Route::prefix('v1')->group(function () {

    // Public Routes
    Route::post('register', [SignUpController::class, 'register']);         // User registration
    Route::post('verify-otp', [SignUpController::class, 'verifyOtp']);     // OTP verification
    Route::post('login', [LoginController::class, 'login']);                // Login
    Route::post('resend-otp', [LoginController::class, 'resendOtp']);      // Resend OTP
    Route::post('forgot-password', [ForgotPasswordController::class, 'forgotPassword']); // Forgot password
    Route::post('reset-password', [ForgotPasswordController::class, 'resetPassword']);   // Reset password

    // Public route (Site settings and content)
    Route::get('web-settings', [WebSettingController::class, 'index']);
    Route::get('hero', [HeroController::class, 'index']);
    Route::get('our-journey', [OurJourneyController::class, 'index']);
    Route::get('about-us', [AboutUsController::class, 'show']);
    Route::get('missions', [OurMissionController::class, 'index']);

    // Contact Form Submission
    Route::post('contact/message', [ContactController::class, 'store']);


    // Public route for users to subscribe
    Route::post('newsletter/subscribe', [NewsletterController::class, 'subscribe']);

    // Article Routes
    Route::get('articles/latest', [ArticleController::class, 'latestArticles']);
    Route::get('testimonials/random', [TestimonialController::class, 'randomTestimonials']);

    //Subscription Payment (Stripe)
    Route::get('/subscription/success', [UserSubscriptionController::class, 'subscriptionSuccess'])->name('booking.success');
    Route::get('/subscription/cancel', [UserSubscriptionController::class, 'subscriptionCancel'])->name('booking.cancel');
    Route::post('/subscription/webhook-handle', [UserSubscriptionController::class, 'handleWebhook'])->name('booking.webhook-handle');

    Route::get('community/posts/landing-page', [CommunityPostController::class, 'landingPageCommunity']);
    Route::get('pages/{slug}', [PageController::class, 'getPagesBySlug']);
    Route::get('pages', [PageController::class, 'index']);

    Route::get('/services', [ServiceController::class, 'indexServices']);

    Route::get('services/landing/page', [ServiceController::class, 'indexServicesLimit']);
    Route::post('services/toggle-status/{id}', [ServiceController::class, 'toggleServiceStatus']);
    Route::get('/faqs', [FaqController::class, 'index']);


    Route::get('teams/landing-page', [TeamController::class, 'teamLandingPage']);

    //Guest-subscription-plan
    Route::get('/guest-subscription-plan', [SubscriptionPlanController::class, 'guestSubscriptionPlan']);

    // Protected Routes (Requires Authentication)
    Route::middleware('auth:sanctum')->group(function () {
        //Subscription Checkout
        Route::post('/subscription-checkout', [UserSubscriptionController::class, 'subscriptionCheckout']);
        Route::get('/check-subscription-by-user', [UserSubscriptionController::class, 'checkSubscriptionByUser']);

        Route::post('logout', [LoginController::class, 'logout']);

        //Personalization
        Route::apiResource('personalized-settings', PersonalizedController::class);


        //Notification Settings
        Route::apiResource('notification-settings', NotificationSettingController::class);

        //Smart Personalized
        Route::apiResource('smart-personalized-settings', SmartPersonalizedController::class);

        //Privacy & Data
        Route::apiResource('privacy-data-settings', PrivacyDataController::class);

        //User Devices
        Route::get('user-devices', [PrivacyDataController::class, 'userDevices']);

        // All notifications for the logged-in user
        Route::get('notification-logged-in', [NotificationController::class, 'notificationLoggedIn']);

        //Notification for Admin
        Route::get('notification-admin', [NotificationController::class, 'notificationAdmin']);
        Route::post('global-notification', [NotificationController::class, 'globalNotification']);
        Route::get('global-notification-list', [NotificationController::class, 'globalNotificationList']);
        Route::delete('delete-global-notification/{id}', [NotificationController::class, 'deleteGlobalNotification']);

        //Mark all as read - Notification
        Route::post('mark-as-read', [NotificationController::class, 'markAsRead']);

        // block unblock user
        Route::post('users/toggle-block/{id}', [UserController::class, 'isBlockedUnblockedTogggle']);

        Route::get('user-stats', [UserController::class, 'getUserManagementStats']);
        Route::get('user-dashboard', [UserController::class, 'userDashboard']);

        // Store and Update User Profile
        Route::post('profiles', [ProfileController::class, 'storeOrUpdate']);
        Route::get('/profiles', [ProfileController::class, 'index']);
        Route::post('profile/upload-document', [ProfileController::class, 'uploadDocument']);

        // Get currently logged-in user profile
        Route::get('my-profile', [ProfileController::class, 'showMyProfile']);
        Route::delete('/profiles/{id}', [ProfileController::class, 'destroy']);

        // Hero Routes
        Route::post('hero', [HeroController::class, 'storeHero']);

        // Mamabot Support Routes
        Route::apiResource('mamabot-supports', MamabotSupportController::class);

        Route::post('web-settings', [WebSettingController::class, 'store']);

        // Article Routes
        Route::post('article-categories', [ArticleController::class, 'storeCategory']);

        Route::get('article-categories', [ArticleController::class, 'indexCategories']);
        Route::delete('article-categories/{id}', [ArticleController::class, 'destroyCategory']);

        // Full CRUD for Articles
        Route::apiResource('articles', ArticleController::class)->except(['show']);
        Route::get('articles/category/{id}', [ArticleController::class, 'getArticlesByCategory']);
        //Route::get('articles/category/{slug}', [ArticleController::class, 'getArticlesByCategoryAll']);
        Route::get('articles/Typebase', [ArticleController::class, 'getTypeBasedArticles']);

        // Service Routes
        Route::post('/services', [ServiceController::class, 'storeService']);
        Route::delete('/services/delete/{id}', [ServiceController::class, 'destroy']);

        // Testimonial Routes
        Route::apiResource('testimonials', TestimonialController::class);


        // Subscription Plan Routes
        Route::apiResource('subscription-plans', SubscriptionPlanController::class);
        Route::post('subscription-plans/toggle-status/{id}', [SubscriptionPlanController::class, 'toggleStatus']);

        // Saved Items Routes
        Route::post('save-item', [SavedItemsController::class, 'toggleSave']);
        Route::get('my-saved-items', [SavedItemsController::class, 'getSavedItems']);

        //Community Groups Routes
        Route::apiResource('community-groups', CommunityGroupsController::class);

        Route::post('groups/join', [CommunityGroupsController::class, 'joinGroup']);
        Route::get('my-groups', [CommunityGroupsController::class, 'myGroups']);


        // Community Posts Routes
        Route::get('community/posts/', [CommunityPostController::class, 'index']);
        Route::post('community/posts/', [CommunityPostController::class, 'store']);
        Route::delete('community/posts/{id}', [CommunityPostController::class, 'destroy']);
        Route::get('community-monitoring', [CommunityPostController::class, 'monitoring']);
        Route::post('community/posts/report', [CommunityPostController::class, 'reportPost']);
        Route::get('reported-content-stats', [CommunityPostController::class, 'getReportedContentStats']);
        Route::post('community/posts/moderate/{id}', [CommunityPostController::class, 'moderatePost']);
        Route::get('my-posts', [CommunityPostController::class, 'myPosts']);
        Route::post('active-deactive-group/{id}', [CommunityGroupsController::class, 'activeDeactiveGroup']);

        // Community Monitoring Stats
        Route::get('community-stats', [CommunityPostController::class, 'getCommunityStats']);

        // Community Interaction Routes
        Route::post('community/like', [CommunityInteractionController::class, 'toggleLike']);
        Route::post('community/comment', [CommunityInteractionController::class, 'storeComment']);
        Route::post('community/share', [CommunityInteractionController::class, 'sharePost']);

        // Team Routes
        Route::post('teams/status/{team}', [TeamController::class, 'isActive']);
        Route::apiResource('teams', TeamController::class);

        //Recovery Log

        Route::apiResource('recovery-logs', RecoveryLogController::class)
            ->only(['store', 'index']);

        //Pelvic log
        Route::apiResource('pelvic-exercise-logs', PelvicExerciseLogController::class)
            ->only(['index', 'store']);

        //PainMovement log
        Route::apiResource('pain-movement-logs', PainMovementLogController::class)
            ->only(['index', 'store']);
        //Recovery feeding log
        Route::apiResource('feeding-logs', FeedingLogController::class)
            ->only(['store', 'index']);
//diaper-log
        Route::apiResource('diaper-log', DiaperLogController::class)->only(['store', 'index']);

        // Sleeping / Sleep tracking
        Route::apiResource('sleep-trackings', SleepTrackingController::class)
            ->only(['store', 'index']);

        //incision-healing-checks
        Route::apiResource('incision-healing-checks', IncisionHealingCheckController::class)
            ->only(['index', 'store']);

//movement-restrictions
        Route::apiResource('movement-restrictions', MovementRestrictionController::class)
            ->only(['index', 'store']);
//baby-movement-logs
        Route::apiResource('baby-movement-logs', BabyMovementLogController::class)
            ->only(['index', 'store']);

//hydration-logs
        Route::apiResource('hydration-logs', HydrationLogController::class)
            ->only(['index', 'store']);
        //mother-wellness-logs
        Route::apiResource('mother-wellness-logs', MotherWellnessLogController::class)
            ->only(['index', 'store']);

        //mental-health-logs
        Route::apiResource('mental-health-logs', MentalHealthLogController::class)->only(['index', 'store']);
//nutrition-logs
        Route::apiResource('nutrition-logs', NutritionLogController::class)
            ->only(['index', 'store']);
//baby-cue-logs
        Route::apiResource('baby-cue-logs', BabyCueLogController::class)
            ->only(['index', 'store']);
        //AI pregnancy-foods
        Route::apiResource('pregnancy-foods', PregnancyFoodController::class)
            ->only(['index', 'store']);
        Route::get('pregnancy-foods/fetch', [PregnancyFoodController::class, 'fetchFoodList']);
        //AI pregnancy-food-weekly-logs
        Route::apiResource('pregnancy-food-weekly-logs', PregnancyFoodWeeklyLogController::class)
            ->only(['index', 'store']);

//pregnancy-food-recipes
        Route::apiResource('pregnancy-food-recipes', PregnancyFoodRecipeController::class)
            ->only(['index', 'store']);

//pregnancy-food-meal-plans
        Route::apiResource('pregnancy-food-meal-plans', PregnancyFoodMealPlanController::class)
            ->only(['index', 'store']);

        // AI Pregnancy Products
        Route::apiResource('pregnancy-products', PregnancyProductController::class)
            ->only(['index', 'store']);
        Route::get('pregnancy-products/fetch', [PregnancyProductController::class, 'fetch']);
//AI daily-insights
        Route::apiResource('daily-insights', DailyInsightController::class)
            ->only(['index', 'store']);

//ai postpartum-daily-tips
        Route::apiResource('postpartum-daily-tips', PostpartumDailyTipController::class)
            ->only(['index', 'store']);
//ai-chat-logs
        Route::apiResource('ai-chat-logs', AiChatLogController::class)
            ->only(['index', 'store']);

        Route::get('ai-chat-logs/user-history', [AiChatLogController::class, 'getUserChatHistory']);

        Route::get('chat-quota', [AiChatLogController::class, 'getChatQuota']);

        // About Us Routes
        Route::post('/about-us/save', [AboutUsController::class, 'save']);
        // Route::delete('/about-us/delete', [AboutUsController::class, 'destroy']);


        // Our Journey Routes
        Route::post('our-journey', [OurJourneyController::class, 'store']);
        Route::delete('our-journey/{id}', [OurJourneyController::class, 'destroy']);


        // Our Mission Routes
        Route::post('missions', [OurMissionController::class, 'store']);
        Route::delete('missions/{id}', [OurMissionController::class, 'destroy']);

        // Show Contact Messages Routes (Admin only)
        Route::get('contact/messages', [ContactController::class, 'index']);
        Route::delete('contact/messages/{id}', [ContactController::class, 'destroy']);


        // Protected route for Admins to view the list
        Route::get('newsletter/list', [NewsletterController::class, 'index']);
        Route::delete('newsletter/delete/{id}', [NewsletterController::class, 'destroy']);

        // pages Routes
        Route::apiResource('pages', PageController::class)->except(['index']);

        // Analytics & Charts
        Route::get('analytics/dashboard', [AnalyticsController::class, 'getDashboardAnalytics']);

        // User Management & Directory
        Route::get('user-management', [UserController::class, 'getUserManagement']);

        // Live QA Doctor Sessions
        Route::post('doctors', [DoctorController::class, 'storeDoctor']);
        Route::post('doctor_update/{id}', [DoctorController::class, 'updateDoctor']);
        Route::delete('doctors/{doctor}', [DoctorController::class, 'destroy']);
        Route::post('doctors/toggle-status/{doctor}', [DoctorController::class, 'toggleActiveStatus']);
        Route::get('doctors', [DoctorController::class, 'getActiveDoctors']);
        Route::post('qa-sessions/register', [QaController::class, 'registerForSession']);
        Route::post('qa-sessions', [QaController::class, 'storeSession']);
        Route::get('qa-sessions', [QaController::class, 'getSessions']);
        Route::post('qa-sessions/{id}', [QaController::class, 'deleteSession']);

        // FAQ Routes
        Route::get('/faqs', [FaqController::class, 'index']);
        Route::post('/faqs/save', [FaqController::class, 'storeOrUpdate']);
        Route::delete('/faqs/{id}', [FaqController::class, 'destroy']);

        //Relaxation Audio Upload

        Route::get('/relaxation-audios', [RelaxationAudioController::class, 'index']);
        Route::get('/relaxation-audios/user-listen', [RelaxationAudioController::class, 'userListen']);
        Route::get('/relaxation-audios/{id}', [RelaxationAudioController::class, 'show']);
        Route::post('/relaxation-audios/upload', [RelaxationAudioController::class, 'uploadAudio']);

        Route::delete('/relaxation-audios/{id}', [RelaxationAudioController::class, 'destroy']);

        Route::get('/popular-topics', [CommunityInteractionController::class, 'getPopularTopics']);

        //Payment information for Logged-in user
        Route::get('/payment-info-by-user', [PaymentController::class, 'getPaymentInfoByUser']);

        //delete-user
        Route::get('/delete-user', [ProfileController::class, 'deleteUser']);

        //Change Password
        Route::post('/change-password', [ProfileController::class, 'changeUserPassword']);

        // Wellness Activities self-care
        Route::post('wellness-activities-save', [WellnessActivityController::class, 'storeOrUpdate']);
        Route::get('wellness-activities', [WellnessActivityController::class, 'getWellnessActivities']);
        Route::delete('wellness-activities/{id}', [WellnessActivityController::class, 'destroy']);



    });
    Route::get('articles/{id}', [ArticleController::class, 'show']);

    //Payments
    Route::apiResource('payments', PaymentController::class);

    //Subscriber List
    Route::get('subscribers', [UserSubscriptionController::class, 'getSubscribers']);

    // Analytics
    Route::get('analytics', [AnalyticsSettingController::class, 'index']);
    Route::get('analytics/{tool}', [AnalyticsSettingController::class, 'showByTool']);
    Route::get('analytics/{id}', [AnalyticsSettingController::class, 'show']);
    Route::post('analytics', [AnalyticsSettingController::class, 'store']);
    Route::put('analytics/{id}', [AnalyticsSettingController::class, 'update']);
    Route::delete('analytics/{id}', [AnalyticsSettingController::class, 'destroy']);
});
