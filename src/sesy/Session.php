<?php
/**
 * Session
 *
 * PHP version 5.4
 *
 * Copyright (c) 2013 mostofreddy <mostofreddy@gmail.com>
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 *
 * @category   Sesy
 * @package    Sesy
 * @subpackage Sesy
 * @author     Federico Lozada Mosto <mostofreddy@gmail.com>
 * @copyright  2013 Federico Lozada Mosto <mostofreddy@gmail.com>
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @link       http://www.mostofreddy.com.ar
 */
namespace sesy;
/**
 * Session
 *
 * Clase para manejar las sesiones en forma OOP, contiene disintos metodos para:
 *
 * - guardar valores en sesion
 * - recuerperar valores
 * - borrar valores
 * - inicializar una sesion (cequeando que no este inicializada anteriormente)
 * - cerrado de sesion y borrado de datos
 * - Setear el directorio donde se almacenan los archivos de sesion
 * - Configurar Memcache como storage de sesion
 *
 * @category   Sesy
 * @package    Sesy
 * @subpackage Sesy
 * @author     Federico Lozada Mosto <mostofreddy@gmail.com>
 * @copyright  2013 Federico Lozada Mosto <mostofreddy@gmail.com>
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @link       http://www.mostofreddy.com.ar
 */
class Session
{
    const ERR_SESSION_VIOLATED = "Invalid session";
    const ERR_INVALID_SESSION_PATH = 'A location "$s" is not a valid directory or not writable';
    const ERR_INVALID_KEY = 'Invalid key. Expected a string';
    protected $keyToSaveToken = 'sesyTokenValidator';
    /**
     * Inicializa una sesion de php
     *
     * @param string $name nombre de la session. Default: sesysession
     *
     * @return void
     */
    public function start($name='sesySessionName')
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_name($name);
            session_start();
            $this->valid();
        }
        return $this;
    }

    /**
     * Regenera el ID de sesion
     *
     * @param bool $regenerate indica si se borra el archivo anterior de sesion. (Default: true)
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function regenerateId($regenerate=true)
    {
        session_regenerate_id($regenerate);
    }
    /**
     * Regenera la sesion actual y dependiendo si se pasa true|false borra el archivo de sesion anterior
     *
     * @access protected
     * @return void
     */
    protected function valid()
    {
        $token = $this->generateToken();
        if (isset($_SESSION[$this->keyToSaveToken])) {
            if ($_SESSION[$this->keyToSaveToken] !== $token) {
                $this->destroy();
                throw new \RuntimeException(static::ERR_SESSION_VIOLATED);
            }
        } else {
            $this->set($this->keyToSaveToken, $token);
            $this->regenerateId(true);
        }
    }

    /**
     * Genera el token de la sesion en base al user_agent y al nombre de sesion
     *
     * @access protected
     * @return void
     */
    protected function generateToken()
    {
        $token = (isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'').session_name();
        return sha1(md5($token));
    }
    /**
     * Destruye una session
     *
     * @return [type]
     */
    public function destroy()
    {
        //inicializa y regenera el id
        $this->regenerateId(true);
        //borra todos los datos de sesion
        $_SESSION = array();
        unset($_SESSION);
        //destruye la sesion
        return session_destroy();
    }
    /**
     * Setea en que directorio se almacenaran las sessiones
     *
     * @param string $path path a directorio
     *
     * @return \sesy\Session
     */
    public function storeInFiles($path)
    {
        $path = escapeshellcmd($path);
        if (!is_dir($path) || !is_writable($path)) {
            throw new \InvalidArgumentException(sprintf(static::ERR_INVALID_SESSION_PATH, $path));
        }
        ini_set('session.save_handler', 'files');
        session_save_path($path);
        return $this;
    }
    /**
     * Setea que las sesiones se guarden en Memcache utilizando los drivers nativos de php
     *
     * @param string $host ip del servidor de mmc. (Default: localhost)
     * @param int    $port puerto del servidor de mmc. (Default: 11211)
     *
     * @return \sesy\Session
     */
    public function storeInMmc($host='localhost', $port=11211)
    {
        $port = (int) $port;
        ini_set('session.save_handler', 'memcache');
        session_save_path("tcp://".$host.":".$port);
        return $this;
    }

    /**
     * Recupera un valor de sesion. Si la clave es null o vacia se devuelve el array de sesion entero
     *
     * @param string $key     clave del valor. (Default: null)
     * @param mixed  $default valor por default si no encuentra la key. (Default: null)
     *
     * @access public
     *
     * @return mixed Value.
     */
    public function get($key=null, $default=null)
    {
        if (!is_string($key) && $key !== null) {
            throw new \InvalidArgumentException(static::ERR_INVALID_KEY);
        }
        if ($key == null) {
            return $_SESSION;
        } else if (array_key_exists($key, $_SESSION)) {
            return $_SESSION[$key];
        }
        return $default;
    }

    /**
     * Setea un valor en sesion
     *
     * @param string $key   Clave donde se almacenara el valor
     * @param mixed  $value Valor a guardar
     *
     * @access public
     *
     * @return \sesy\Session
     */
    public function set($key, $value)
    {
        if (!is_string($key)) {
            throw new \InvalidArgumentException(static::ERR_INVALID_KEY);
        }
        $_SESSION[$key] = $value;
        return $this;
    }

    /**
     * Borra una variable de sesion
     *
     * @param string $key Clave del valor a borrar
     *
     * @access public
     *
     * @return \sesy\Session
     */
    public function delete($key)
    {
        if (!is_string($key)) {
            throw new \InvalidArgumentException(static::ERR_INVALID_KEY);
        }
        if (array_key_exists($key, $_SESSION)) {
            unset($_SESSION[$key]);
        }
        return $this;
    }
}
