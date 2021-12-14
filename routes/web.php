<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */
header('Access-Control-Allow-Origin:  *');
header('Access-Control-Allow-Methods:  POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers:  Content-Type, X-Auth-Token, Origin, Authorization');
Route::get('/', function () {
    //    return view('welcome');
    return 1;
});

Route::group(['middleware' => 'CrossBrowserSecurityDisabled'], function () {
    //v1
    Route::group(['prefix' => 'users', 'namespace' => 'User'], function () {
        Route::post('login', 'Login_SignupController@login');
        Route::post('register-user', 'Login_SignupController@registerUser');
        Route::get('activate', 'Login_SignupController@activateUser');
        Route::get('check-email', 'Login_SignupController@checkEmail');

        Route::get('forgot-password', 'ForgotPassword@forgotPassword');
        Route::post('change-password-controller', 'ForgotPassword@changePassword');
    });

    Route::group(['middleware' => 'validToken'], function () {

        //health tag entry route
        Route::group(['prefix' => 'healthentry', 'namespace' => 'HealthEntry'], function () {

            Route::post('create-health-entry', 'HealthEntryController@createHealthEntry');
            Route::get('get-health-tags', 'HealthEntryController@getHealthTags');
            Route::get('get-health-entry', 'HealthEntryController@getHealthEntry');
            Route::post('get-health-entry-range', 'HealthEntryController@getHealthEntryRange');
            Route::post('delete-tag', 'HealthEntryController@deleteTag');
        });

        Route::group(['prefix' => 'users', 'namespace' => 'User'], function () {
            Route::post('register-fcm', 'ProfileController@registerFCM');
            Route::post('edit-profile', 'ProfileController@editProfile');
            Route::get('view-profile', 'ProfileController@viewProfile');
            Route::post('add-user-doc', 'ProfileController@addDocUser');
            Route::get('jwt-auth', 'ProfileController@jwtAuth');
        });

        Route::group(['prefix' => 'workout', 'namespace' => 'Workout'], function () {
            Route::post('create-or-edit-workout', 'WorkoutController@createOrEditWorkout');
            Route::get('view-workout', 'WorkoutController@viewWorkout');
            Route::get('view-stats', 'WorkoutController@getWeeksStats');
            Route::get('view-home-screen', 'WorkoutController@getHomeStats');
            Route::get('view-training-load-graph', 'WorkoutController@getTraningloadStats');
            Route::get('view-medical_report', 'WorkoutController@getMedicalStats');
            Route::get('request-s3-url', 'Awss3@getSignedUrl');
            Route::get('request-s3-logs-url', 'Awss3@getGenericSignedUrl');
            Route::get('request-s3-ble-url', 'Awss3@getBleGenericSignedUrl');
            Route::get('request-s3-all-bin-url', 'Awss3@getAllBinFileSignedUrl');
            Route::get('request-s3-medical-url', 'Awss3@getMedicalGenericSignedUrl');
            Route::get('get-file', 'Awss3@getFile');

            //data sync APIs
            Route::get('get-updated-workouts', 'WorkoutController@getUpdatedWorkouts');
            Route::post('send-updated-workouts', 'WorkoutController@sendUpdatedWorkouts');

            Route::group(['prefix' => 'notes'], function () {
                Route::post('create-note/{workout}', 'NotesController@addNote');
                Route::post('edit-note/{workout}', 'NotesController@editNote');
                Route::post('delete-note/{workout}', 'NotesController@deleteNote');
                Route::post('create-note-doc', 'NotesController@docAddNote');
                Route::post('edit-note-doc', 'NotesController@docEditNote');
                Route::post('delete-note-doc', 'NotesController@docDeleteNote');
                Route::post('view-note-doc', 'NotesController@viewDocNote');
                Route::post('pinned-note-doc', 'NotesController@pinnedDocNote');

            });
        });

        Route::group(['prefix' => 'insights', 'namespace' => 'Insight'], function () {
            Route::get('search', 'InsightManager@search');
        });

        Route::group(['prefix' => 'device', 'namespace' => 'Device'], function () {
            Route::post('register', 'RegisterDevice@registerdevice');
        });

        Route::group(['middleware' => 'CheckForAdminRole', 'prefix' => 'admin', 'namespace' => 'Admin'], function () {
            Route::get('users/get-all-users', 'Users@getAllUserList');
            Route::post('users/edit/{editableUser}', 'Users@editUser');
            Route::group(['prefix' => 'workout', 'namespace' => 'Workout'], function () {
                Route::get('view-workout/{viewableUser}', 'WorkoutController@viewUsersWorkout');
                Route::get('view-stats/{viewableUser}', 'WorkoutController@getUsersWeeksStats');
                Route::get('get-file/{viewableUser}', 'Awss3@getFile');

                Route::post('edit-workout/{viewableUser}', 'WorkoutController@editWorkout');

                Route::group(['prefix' => 'notes'], function () {
                    Route::post('create-note/{workout}', 'NotesController@addNote');
                    Route::post('edit-note/{workout}', 'NotesController@editNote');
                    Route::post('delete-note/{workout}', 'NotesController@deleteNote');
                });
            });
        });
    });

    Route::group(['prefix' => 'enums', 'namespace' => 'Common'], function () {
        Route::get('get-by-group', 'Enums@getEnumValuesByGroup');
        Route::get('get-by-name', 'Enums@getEnumValuesByName');
        Route::get('get-by-id', 'Enums@getEnumValuesById');
        Route::get('get-php-info', 'Enums@getPhpInfo');
    });

    Route::group(['prefix' => 'common', 'namespace' => 'Common'], function () {
        Route::get('check-firmware-update', 'Appconfig@checkFirmwareUpdate');
    });

    Route::group(['prefix' => 'community', 'namespace' => 'Community'], function () {
        Route::get('posts/search', 'PostManager@search');
    });

    Route::group(['prefix' => 'common', 'namespace' => 'Common'], function () {
        Route::get('get-current-policy-acceptance', 'Policies@getPolicyAcceptance');
    });
    Route::group(['middleware' => 'validToken'], function () {
        Route::group(['prefix' => 'common', 'namespace' => 'Common'], function () {
            Route::get('check-apk-update', 'Appconfig@forceUpdateApp');
            Route::post('update-policy-acceptance', 'Policies@updatePolicyAcceptance');
        });
        Route::group(['prefix' => 'garmin', 'namespace' => 'Garmin'], function () {
            Route::post('user/register', 'User@registerGarmin');
        });

        Route::group(['prefix' => 'policies', 'namespace' => 'Common'], function () {
            Route::post('add-policy', 'Policies@addNewPolicy');
            Route::post('remove-policy', 'Policies@removePolicy');
            Route::post('remove-user-acceptance-record', 'Policies@removeUserAcceptance');
            Route::get('get-user-acceptance-record', 'Policies@getUserAcceptance');
        });
    });
    Route::group(['prefix' => 'garmin', 'namespace' => 'Garmin'], function () {
        Route::any('push/activity-summary', 'PushApi@receiveActivitiesSummaryPush');
        Route::any('push/activity-details', 'PushApi@receiveActivitiesDetailsPush');
        Route::any('user/deregister', 'PushApi@receiveDeregistration');
    });
    //---------------------------------VERSION 2 ROUTES ------------------------
    Route::group(['prefix' => 'v2'], function () {
        Route::group(['prefix' => 'users', 'namespace' => 'V2\User'], function () {
            Route::post('login', 'Login_SignupController@login');
            Route::post('register-user', 'Login_SignupController@registerUser');
            Route::get('activate', 'Login_SignupController@activateUser');
            Route::get('check-email', 'Login_SignupController@checkEmail');

            Route::get('forgot-password', 'ForgotPassword@forgotPassword');
            Route::post('change-password-controller', 'ForgotPassword@changePassword');
        });

        Route::group(['prefix' => 'enums', 'namespace' => 'V2\Common'], function () {
            Route::get('get-by-group', 'Enums@getEnumValuesByGroup');
            Route::get('get-by-name', 'Enums@getEnumValuesByName');
            Route::get('get-by-id', 'Enums@getEnumValuesById');
            Route::get('get-php-info', 'Enums@getPhpInfo');
        });

        Route::group(['prefix' => 'common', 'namespace' => 'V2\Common'], function () {
            Route::get('check-firmware-update', 'Appconfig@checkFirmwareUpdate');
        });

        Route::group(['prefix' => 'community', 'namespace' => 'V2\Community'], function () {
            Route::get('posts/search', 'PostManager@search');
        });

        Route::group(['middleware' => 'validToken'], function () {
            Route::group(['prefix' => 'workout', 'namespace' => 'V2\Workout'], function () {
                Route::post('create-workout', 'WorkoutController@createWorkout');
                Route::post('edit-workout', 'WorkoutController@editWorkout');
                Route::patch('edit-workout', 'WorkoutController@editWorkout');
                Route::post('delete-workout', 'WorkoutController@deleteWorkout');
                Route::delete('delete-workout', 'WorkoutController@deleteWorkout');
                Route::get('get-file', 'Awss3@getFile');
                Route::get('view-workout', 'WorkoutController@viewWorkout');
                Route::get('view-stats', 'WorkoutController@getWeeksStats');
                Route::get('view-home-screen', 'WorkoutController@getHomeStats');
                Route::get('view-training-load-graph', 'WorkoutController@getTraningloadStats');
                Route::get('request-s3-url', 'Awss3@getSignedUrl');
                Route::get('request-fitfile', 'FitFile@getFile');

                //data sync APIs
                Route::get('get-updated-workouts', 'WorkoutController@getUpdatedWorkouts');
                Route::post('send-updated-workouts', 'WorkoutController@sendUpdatedWorkouts');

                Route::group(['prefix' => 'notes'], function () {
                    Route::post('create-note/{workout}', 'NotesController@addNote');
                    Route::post('edit-note/{workout}', 'NotesController@editNote');
                    Route::post('delete-note/{workout}', 'NotesController@deleteNote');
                });
            });
            Route::group(['prefix' => 'users', 'namespace' => 'V2\User'], function () {
                Route::post('edit-profile', 'ProfileController@editProfile');
                Route::get('view-profile', 'ProfileController@viewProfile');
            });

            Route::group(['prefix' => 'insights', 'namespace' => 'V2\Insight'], function () {
                Route::get('search', 'InsightManager@search');
            });

            Route::group(['prefix' => 'healthentry', 'namespace' => 'V2\HealthEntry'], function () {
                Route::get('get-health-tags', 'HealthEntryController@getHealthTags');
                Route::post('create-health-entry', 'HealthEntryController@createHealthEntry');

            });
        });
    });

    //---------------------------------VERSION 3 ROUTES ------------------------
    Route::group(['prefix' => 'v3'], function () {
        Route::group(['prefix' => 'users', 'namespace' => 'V3\User'], function () {
            Route::post('register-user', 'Login_SignupController@registerUser');
            Route::post('edit-profile', 'ProfileController@editProfile');
            //            Route::get('activate', 'Login_SignupController@activateUser');
            //            Route::get('check-email', 'Login_SignupController@checkEmail');
            //
            //            Route::get('forgot-password', 'ForgotPassword@forgotPassword');
            //            Route::post('change-password-controller', 'ForgotPassword@changePassword');
            //
            //
        });
        //
        //        Route::group(['prefix' => 'enums', 'namespace' => 'V2\Common'], function () {
        //            Route::get('get-by-group', 'Enums@getEnumValuesByGroup');
        //            Route::get('get-by-name', 'Enums@getEnumValuesByName');
        //            Route::get('get-by-id', 'Enums@getEnumValuesById');
        //            Route::get('get-php-info', 'Enums@getPhpInfo');
        //        });

        //        Route::group(['prefix' => 'common', 'namespace' => 'V2\Common'], function () {
        //            Route::get('check-firmware-update', 'Appconfig@checkFirmwareUpdate');
        //        });

        //        Route::group(['prefix' => 'community', 'namespace' => 'V2\Community'], function () {
        //            Route::get('posts/search', 'PostManager@search');
        //        });

        //        Route::group(['prefix' => 'garmin', 'namespace' => 'V2\Garmin'], function () {
        //            Route::any('push/activities', 'PushApi@receiveActivitiesPush');
        //        });

        Route::group(['middleware' => 'validToken'], function () {
            Route::group(['prefix' => 'workout', 'namespace' => 'V3\Workout'], function () {
                Route::post('create-workout', 'WorkoutController@createWorkout');
                Route::post('edit-workout', 'WorkoutController@editWorkout');
                Route::patch('edit-workout', 'WorkoutController@editWorkout');
                Route::post('delete-workout', 'WorkoutController@deleteWorkout');
                Route::delete('delete-workout', 'WorkoutController@deleteWorkout');
                //                Route::get('get-file','Awss3@getFile');
                Route::get('view-workout', 'WorkoutController@viewWorkout');
                Route::get('view-ttlworkout', 'WorkoutController@viewTTLWorkout');
                Route::get('get-updated-workouts', 'WorkoutController@getUpdatedWorkouts');
                Route::get('view-home-screen', 'WorkoutController@getHomeStats');
                //                Route::get('view-stats', 'WorkoutController@getWeeksStats');
                //                Route::get('request-s3-url', 'Awss3@getSignedUrl');
                //                Route::get('request-fitfile', 'FitFile@getFile');
            });
            //            Route::group(['prefix' => 'users', 'namespace' => 'V2\User'], function () {
            //                Route::post('edit-profile', 'ProfileController@editProfile');
            //                Route::get('view-profile', 'ProfileController@viewProfile');
            //            });
            //
            //            Route::group(['prefix' => 'insights', 'namespace' => 'V2\Insight'], function () {
            //                Route::get('search', 'InsightManager@search');
            //
            //            });
        });
    });

    //---------------------------------VERSION 4 ROUTES ------------------------
    Route::group(['prefix' => 'v4'], function () {
        Route::group(['middleware' => 'validToken'], function () {
            Route::group(['prefix' => 'workout', 'namespace' => 'V4\Workout'], function () {
                Route::post('create-workout', 'WorkoutController@createWorkout');
                Route::get('view-workout', 'WorkoutController@viewWorkout');
                Route::post('delete-workout', 'WorkoutController@deleteWorkout');
            });
        });
        Route::group(['prefix' => 'users', 'namespace' => 'V4\User'], function () {
            //            Route::post('login', 'Login_SignupController@login');
            Route::post('register-user', 'Login_SignupController@registerUser');
            //            Route::get('activate', 'Login_SignupController@activateUser');
            //            Route::get('check-email', 'Login_SignupController@checkEmail');
            //
            //            Route::get('forgot-password', 'ForgotPassword@forgotPassword');
            //            Route::post('change-password-controller', 'ForgotPassword@changePassword');
            //
            //
        });
    });
    Route::group(['prefix' => 'v5'], function () {
        Route::group(['middleware' => 'validToken'], function () {
            Route::group(['prefix' => 'workout', 'namespace' => 'V5\Workout'], function () {
                Route::post('create-workout', 'WorkoutController@createWorkout');
                Route::get('view-workout', 'WorkoutController@viewWorkout');
            });
        });
    });

    Route::group(['prefix' => 'v6'], function () {
        Route::group(['middleware' => 'validToken'], function () {
            Route::group(['prefix' => 'workout', 'namespace' => 'V6\Workout'], function () {
                Route::post('create-workout', 'WorkoutController@createWorkout');
                Route::get('view-workout', 'WorkoutController@viewWorkout');
            });
        });
    });

    Route::group(['prefix' => 'v7'], function () {
        Route::group(['middleware' => 'validToken'], function () {
            Route::group(['prefix' => 'workout', 'namespace' => 'V7\Workout'], function () {
                Route::post('create-workout', 'WorkoutController@createWorkout');
            });
        });
    });


    //Retained
    Route::group(['prefix' => 'users', 'namespace' => 'User'], function () {
        Route::get('confirmation-message', function () {
            return view('confirmationMessage');
        });
        Route::get('change-password', function () {
            return view('changePassword');
        });
        Route::get('reset-password', function () {
            return view('mails/resetPassword');
        });
    });

    Route::group(['prefix' => 'cron'], function () {
        Route::get('calculate-tpst', 'Cron@calculateTrainingStrain');
        Route::get('get-post-thumbnails', 'Cron@makePostThumbnails');
        Route::get('generate-workout-time', 'Cron@generateWorkoutTime');
    });
});

// Route::get('/mail', function () {
//     return view('mails.resetPassword', ['name' => 'James']);
// });
