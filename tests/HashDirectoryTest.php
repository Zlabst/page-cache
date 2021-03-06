<?php
/**
 * This file is part of the PageCache package.
 *
 * @author Muhammed Mamedov <mm@turkmenweb.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PageCache\Tests;

use PageCache\HashDirectory;
use org\bovigo\vfs\vfsStream;

/**
 * HasDirectory creates directories based on cache filename to be used in storage
 * Virtual directory structure is used in testing
 *
 * Class HashDirectoryTest
 * @package PageCache\Tests
 */
class HashDirectoryTest extends \PHPUnit_Framework_TestCase
{

    private $dir;
    private $filename;

    /** @var  HashDirectory */
    private $hd;

    public function setUp()
    {
        //setup virtual dir
        vfsStream::setup('tmpdir');
        $this->dir = vfsStream::url('tmpdir').'/';

        //dummy file name for testing
        $this->filename = '18a3938de0087a87d3530084cd46edf4';
        $this->hd = new HashDirectory($this->filename, $this->dir);
    }

    public function tearDown()
    {
        unset($this->hd);
    }

    public function testGetHash()
    {
        $val1 = ord('8'); //56
        $val2 = ord('3'); //51

        //normalize to 99
        $val1 = $val1 % 99; //56
        $val2 = $val2 % 99; //51

        $returned = $val1 . '/' . $val2 . '/';

        $this->assertEquals($returned, $this->hd->getHash());
        $this->assertFileExists($this->dir . '56/51');
        $this->assertEquals($returned, $this->hd->getLocation($this->filename));

        //new object
        $newFilename = '93f0938de0087a87d3530084cd46edf4';
        $newHd = new HashDirectory($newFilename, $this->dir);

        $this->assertFileNotExists($this->dir . '51/48');
        $this->assertEquals('51/48/', $newHd->getHash());
        $this->assertAttributeEquals('93f0938de0087a87d3530084cd46edf4', 'file', $newHd);
        $this->assertFileExists($this->dir . '51/48');
    }

    public function testGetLocation()
    {
        $this->assertEquals('56/51/', $this->hd->getLocation($this->filename));
    }

    public function testGetLocationEmptyFilename()
    {
        $this->assertNull($this->hd->getLocation(''));
    }

    public function testCreateSubDirsWithExistingDirectory()
    {
        //lets create first dir ->56, and leave 51 uncreated
        mkdir($this->dir.'56');
        $this->assertNotEmpty($this->hd->getHash());
    }

    /**
     * @expectedException \Exception
     */
    public function testCreateSubDirsWithException()
    {
        //make cache directory non writable, this will prevent from them being created
        chmod($this->dir, '000');
        $this->hd->getHash();
    }

    /**
     * @expectedException \Exception
     */
    public function testConstructorException()
    {
        new HashDirectory('false file', 'false directory');
    }
}
