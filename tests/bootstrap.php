<?php
/**
 * SuiteCRM is a customer relationship management program developed by SalesAgility Ltd.
 * Copyright (C) 2021 SalesAgility Ltd.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SALESAGILITY, SALESAGILITY DISCLAIMS THE
 * WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License
 * version 3, these Appropriate Legal Notices must retain the display of the
 * "Supercharged by SuiteCRM" logo. If the display of the logos is not reasonably
 * feasible for technical reasons, the Appropriate Legal Notices must display
 * the words "Supercharged by SuiteCRM".
 */


include __DIR__.'/../vendor/autoload.php'; // composer autoload

use AspectMock\Kernel;

$kernel = Kernel::getInstance();
$kernel->init([
    'debug' => true,
    'appDir' => __DIR__ . '/..',
    'cacheDir' => __DIR__ . '/../cache/test/aop',
    'includePaths' => [
        __DIR__ . '/../core/backend',
        __DIR__ . '/../vendor/api-platform',
    ],
    'excludePaths' => [
        __DIR__,
        '/../vendor/codeception', '/../vendor/phpunit'
    ],
]);

$kernel->loadFile(__DIR__ . '/../core/backend/Engine/LegacyHandler/AclHandler.php');
$kernel->loadFile(__DIR__ . '/../core/backend/Authentication/LegacyHandler/Authentication.php');
$kernel->loadFile(__DIR__ . '/../vendor/api-platform/core/src/Metadata/Resource/ResourceMetadata.php');
$kernel->loadFile(__DIR__ . '/../vendor/api-platform/core/src/Util/RequestAttributesExtractor.php');
$kernel->loadFile(__DIR__ . '/../vendor/api-platform/core/src/Metadata/Resource/Factory/AnnotationResourceFilterMetadataFactory.php');
$kernel->loadFile(__DIR__ . '/../vendor/api-platform/core/src/Security/EventListener/DenyAccessListener.php');
