<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

return [
	'ocs' => [
		[
			'name' => 'API#generate',
			'url' => '/api/v1/generate',
			'verb' => 'GET',
		],
		[
			'name' => 'API#validate',
			'url' => '/api/v1/validate',
			'verb' => 'POST',
		]
	]
];
