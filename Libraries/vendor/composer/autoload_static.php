<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInita44e2e6dfee05c35c23d4524e759b87e
{
    public static $prefixLengthsPsr4 = array (
        'c' => 
        array (
            'cweagans\\Composer\\' => 18,
        ),
        'P' => 
        array (
            'Psr\\Log\\' => 8,
        ),
        'M' => 
        array (
            'Mautic\\' => 7,
        ),
        'E' => 
        array (
            'Escopecz\\MauticFormSubmit\\' => 26,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'cweagans\\Composer\\' => 
        array (
            0 => __DIR__ . '/..' . '/cweagans/composer-patches/src',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'Mautic\\' => 
        array (
            0 => __DIR__ . '/..' . '/mautic/api-library/lib',
        ),
        'Escopecz\\MauticFormSubmit\\' => 
        array (
            0 => __DIR__ . '/..' . '/escopecz/mautic-form-submit/src',
        ),
    );

    public static $classMap = array (
        'Escopecz\\MauticFormSubmit\\Cookie' => __DIR__ . '/..' . '/escopecz/mautic-form-submit/src/Cookie.php',
        'Escopecz\\MauticFormSubmit\\HttpHeader' => __DIR__ . '/..' . '/escopecz/mautic-form-submit/src/HttpHeader.php',
        'Escopecz\\MauticFormSubmit\\Mautic' => __DIR__ . '/..' . '/escopecz/mautic-form-submit/src/Mautic.php',
        'Escopecz\\MauticFormSubmit\\Mautic\\Contact' => __DIR__ . '/..' . '/escopecz/mautic-form-submit/src/Mautic/Contact.php',
        'Escopecz\\MauticFormSubmit\\Mautic\\Cookie' => __DIR__ . '/..' . '/escopecz/mautic-form-submit/src/Mautic/Cookie.php',
        'Escopecz\\MauticFormSubmit\\Mautic\\Form' => __DIR__ . '/..' . '/escopecz/mautic-form-submit/src/Mautic/Form.php',
        'Mautic\\Api\\Api' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/Api.php',
        'Mautic\\Api\\Assets' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/Assets.php',
        'Mautic\\Api\\CampaignEvents' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/CampaignEvents.php',
        'Mautic\\Api\\Campaigns' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/Campaigns.php',
        'Mautic\\Api\\Categories' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/Categories.php',
        'Mautic\\Api\\Companies' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/Companies.php',
        'Mautic\\Api\\CompanyFields' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/CompanyFields.php',
        'Mautic\\Api\\ContactFields' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/ContactFields.php',
        'Mautic\\Api\\Contacts' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/Contacts.php',
        'Mautic\\Api\\Data' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/Data.php',
        'Mautic\\Api\\Devices' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/Devices.php',
        'Mautic\\Api\\DynamicContents' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/DynamicContents.php',
        'Mautic\\Api\\Emails' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/Emails.php',
        'Mautic\\Api\\Files' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/Files.php',
        'Mautic\\Api\\Focus' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/Focus.php',
        'Mautic\\Api\\Forms' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/Forms.php',
        'Mautic\\Api\\Leads' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/Leads.php',
        'Mautic\\Api\\Lists' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/Lists.php',
        'Mautic\\Api\\Messages' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/Messages.php',
        'Mautic\\Api\\Notes' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/Notes.php',
        'Mautic\\Api\\Notifications' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/Notifications.php',
        'Mautic\\Api\\Pages' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/Pages.php',
        'Mautic\\Api\\PointTriggers' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/PointTriggers.php',
        'Mautic\\Api\\Points' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/Points.php',
        'Mautic\\Api\\Reports' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/Reports.php',
        'Mautic\\Api\\Roles' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/Roles.php',
        'Mautic\\Api\\Segments' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/Segments.php',
        'Mautic\\Api\\Smses' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/Smses.php',
        'Mautic\\Api\\Stages' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/Stages.php',
        'Mautic\\Api\\Stats' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/Stats.php',
        'Mautic\\Api\\Tags' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/Tags.php',
        'Mautic\\Api\\Themes' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/Themes.php',
        'Mautic\\Api\\Tweets' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/Tweets.php',
        'Mautic\\Api\\Users' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/Users.php',
        'Mautic\\Api\\Webhooks' => __DIR__ . '/..' . '/mautic/api-library/lib/Api/Webhooks.php',
        'Mautic\\Auth\\AbstractAuth' => __DIR__ . '/..' . '/mautic/api-library/lib/Auth/AbstractAuth.php',
        'Mautic\\Auth\\ApiAuth' => __DIR__ . '/..' . '/mautic/api-library/lib/Auth/ApiAuth.php',
        'Mautic\\Auth\\AuthInterface' => __DIR__ . '/..' . '/mautic/api-library/lib/Auth/AuthInterface.php',
        'Mautic\\Auth\\BasicAuth' => __DIR__ . '/..' . '/mautic/api-library/lib/Auth/BasicAuth.php',
        'Mautic\\Auth\\OAuth' => __DIR__ . '/..' . '/mautic/api-library/lib/Auth/OAuth.php',
        'Mautic\\Exception\\AbstractApiException' => __DIR__ . '/..' . '/mautic/api-library/lib/Exception/AbstractApiException.php',
        'Mautic\\Exception\\ActionNotSupportedException' => __DIR__ . '/..' . '/mautic/api-library/lib/Exception/ActionNotSupportedException.php',
        'Mautic\\Exception\\AuthorizationRequiredException' => __DIR__ . '/..' . '/mautic/api-library/lib/Exception/AuthorizationRequiredException.php',
        'Mautic\\Exception\\ContextNotFoundException' => __DIR__ . '/..' . '/mautic/api-library/lib/Exception/ContextNotFoundException.php',
        'Mautic\\Exception\\IncorrectParametersReturnedException' => __DIR__ . '/..' . '/mautic/api-library/lib/Exception/IncorrectParametersReturnedException.php',
        'Mautic\\Exception\\RequiredParameterMissingException' => __DIR__ . '/..' . '/mautic/api-library/lib/Exception/RequiredParameterMissingException.php',
        'Mautic\\Exception\\UnexpectedResponseFormatException' => __DIR__ . '/..' . '/mautic/api-library/lib/Exception/UnexpectedResponseFormatException.php',
        'Mautic\\MauticApi' => __DIR__ . '/..' . '/mautic/api-library/lib/MauticApi.php',
        'Mautic\\QueryBuilder\\QueryBuilder' => __DIR__ . '/..' . '/mautic/api-library/lib/QueryBuilder/QueryBuilder.php',
        'Mautic\\QueryBuilder\\WhereBuilder' => __DIR__ . '/..' . '/mautic/api-library/lib/QueryBuilder/WhereBuilder.php',
        'Mautic\\Response' => __DIR__ . '/..' . '/mautic/api-library/lib/Response.php',
        'Psr\\Log\\AbstractLogger' => __DIR__ . '/..' . '/psr/log/Psr/Log/AbstractLogger.php',
        'Psr\\Log\\InvalidArgumentException' => __DIR__ . '/..' . '/psr/log/Psr/Log/InvalidArgumentException.php',
        'Psr\\Log\\LogLevel' => __DIR__ . '/..' . '/psr/log/Psr/Log/LogLevel.php',
        'Psr\\Log\\LoggerAwareInterface' => __DIR__ . '/..' . '/psr/log/Psr/Log/LoggerAwareInterface.php',
        'Psr\\Log\\LoggerAwareTrait' => __DIR__ . '/..' . '/psr/log/Psr/Log/LoggerAwareTrait.php',
        'Psr\\Log\\LoggerInterface' => __DIR__ . '/..' . '/psr/log/Psr/Log/LoggerInterface.php',
        'Psr\\Log\\LoggerTrait' => __DIR__ . '/..' . '/psr/log/Psr/Log/LoggerTrait.php',
        'Psr\\Log\\NullLogger' => __DIR__ . '/..' . '/psr/log/Psr/Log/NullLogger.php',
        'Psr\\Log\\Test\\DummyTest' => __DIR__ . '/..' . '/psr/log/Psr/Log/Test/LoggerInterfaceTest.php',
        'Psr\\Log\\Test\\LoggerInterfaceTest' => __DIR__ . '/..' . '/psr/log/Psr/Log/Test/LoggerInterfaceTest.php',
        'cweagans\\Composer\\PatchEvent' => __DIR__ . '/..' . '/cweagans/composer-patches/src/PatchEvent.php',
        'cweagans\\Composer\\PatchEvents' => __DIR__ . '/..' . '/cweagans/composer-patches/src/PatchEvents.php',
        'cweagans\\Composer\\Patches' => __DIR__ . '/..' . '/cweagans/composer-patches/src/Patches.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInita44e2e6dfee05c35c23d4524e759b87e::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInita44e2e6dfee05c35c23d4524e759b87e::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInita44e2e6dfee05c35c23d4524e759b87e::$classMap;

        }, null, ClassLoader::class);
    }
}
