<?php

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

$EM_CONF[$_EXTKEY] = array(
    'title' => 'Facebook Import',
    'description' => 'Enables import of Facebook information.',
    'category' => 'backend',
    'version' => '1.0.0',
    'state' => 'beta',
    'clearcacheonload' => false,
    'author' => 'Daniel Siepmann',
    'author_email' => 'd.siepmann@web-vision.de',
    'author_company' => 'web-vision GmbH',
    'constraints' => array(
        'depends' => array(
            'typo3' => '6.1.9',
            'scheduler' => '',
            'news' => '',
        ),
    ),
);
