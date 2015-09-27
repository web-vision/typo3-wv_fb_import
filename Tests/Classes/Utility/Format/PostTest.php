<?php
namespace WebVision\WvFbImport\Tests\Classes\Utility\Format;

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

/**
 *
 * @author Daniel Siepmann <d.siepmann@web-vision.de>
 */
class PostTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/Classes/Utility/Format/Post.php';
        $this->formatClass = new \WebVision\WvFbImport\Utility\Format\Post;
    }

    /**
     * @test
     *
     * @return void
     */
    public function formatDateTimeTest()
    {
        $post = new \stdClass();
        $post->created_time = date('c');

        $this->assertEquals(time(), $this->formatClass->formatDateTime($post));
    }

    /**
     * @test
     *
     * @return void
     */
    public function getPostUrlTest()
    {
        $post = new \stdClass();
        $post->id = '3453536345_3426562342';
        $pageId = 'web-vision';

        $this->assertEquals(
            'https://www.facebook.com/web-vision/posts/3426562342',
            $this->formatClass->getPostUrl($post, $pageId)
        );
    }
}
