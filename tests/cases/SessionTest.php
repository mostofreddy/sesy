<?php
/**
 * SessionTest
 *
 * PHP version 5.4
 *
 * Copyright (c) 2013 mostofreddy <mostofreddy@gmail.com>
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 *
 * @category   Test
 * @package    Sesy
 * @subpackage Tests\Cases
 * @author     Federico Lozada Mosto <mostofreddy@gmail.com>
 * @copyright  2013 Federico Lozada Mosto <mostofreddy@gmail.com>
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @link       http://www.mostofreddy.com.ar
 */
namespace sesy\tests\cases;
/**
 * Test unitario para testear el seteo del path donde se almacenan las sesiones en filesystem
 *
 * @category   Test
 * @package    Sesy
 * @subpackage Tests\Cases
 * @author     Federico Lozada Mosto <mostofreddy@gmail.com>
 * @copyright  2013 Federico Lozada Mosto <mostofreddy@gmail.com>
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @link       http://www.mostofreddy.com.ar
 */
class SessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Helper. Genera un token valido
     *
     * @access protected
     * @return string
     */
    protected function generateToken()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla';
        ini_set("session.name", "sesyTest");
        return sha1(md5($_SERVER['HTTP_USER_AGENT']."sesyTest"));
    }
    /**
     * Testea el metood generateToken de la clase \sesy\Session
     *
     * @access public
     * @return void
     */
    public function testGenerateToken()
    {
        $expected = $this->generateToken();
        $ses = new \sesy\Session();
        $class = new \ReflectionClass($ses);
        $method = $class->getMethod('generateToken');
        $method->setAccessible(true);
        $this->assertEquals(
            $expected,
            $method->invoke($ses)
        );
    }

    /**
     * Testea pasar un directorio invalido a la funcion storeInFiles de la clase \sesy\Session y lance una excepcion
     *
     * @access public
     * @return void
     */
    public function testStoreInFilesFail()
    {
        $obj = new \sesy\Session();

        $path = realpath(__DIR__."/../")."/invalidFolder";
        try {
            $obj->storeInFiles($path);
        } catch (\Exception $e) {
            $this->assertEquals(
                sprintf(\sesy\Session::ERR_INVALID_SESSION_PATH, $path),
                $e->getMessage()
            );
        }
    }
    /**
     * Testea la funcion storeInFiles de la clase \sesy\Session
     *
     * @access public
     * @return void
     */
    public function testStoreInFiles()
    {
        $path = "/tmp";
        $obj = new \sesy\Session();
        $obj->storeInFiles($path);
        $this->assertEquals($path, ini_get("session.save_path"));
    }
    /**
     * Testea configurar las sesiones con mmc
     *
     * @access  public
     * @return void
     */
    public function testStoreInMmc()
    {
        $obj = new \sesy\Session();
        $obj->storeInMmc();
        $this->assertEquals('memcache', ini_get("session.save_handler"));
        $this->assertEquals('tcp://localhost:11211', ini_get("session.save_path"));
    }

    /**
     * Testea el metodo destroy
     *
     * @access public
     *
     * @return void
     */
    public function testDestroy()
    {
        $obj = new \sesy\Session();
        $obj->start();
        $obj->destroy();
        $this->assertEquals(
            session_status(),
            1
        );
    }
}
