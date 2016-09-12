<?php
namespace Zanra\Framework;

class Session
{
    /**
     * @var bool
     */
    private $started = false;
  
    /**
     * @var bool
     */
    private $closed = false;
    
    /**
     * @var string
     */
    private $flashname = null;
  
    /**
     * @var \Zanra\Framework\Flash
     */
    private $flash;
    
    /**
     * Constructor.
     */
    public function __Construct()
    { 
        $this->flash = new \Zanra\Framework\Flash();
        $this->flashname = $this->flash->getName();
    }
    
    /**
     * Start a new session.
     */
    public function start()
    {
        if ($this->started) {
            return true;
        }
        
        // This prevents PHP from attempting to send the headers again
        // when session_write_close is called
        if ($this->closed) {
            ini_set('session.use_only_cookies', false);
            ini_set('session.use_cookies', false);
            ini_set('session.cache_limiter', null);
        }
        
        if (!session_start()) {
            throw new \RuntimeException('failed to start the session');
        }
        
        $_SESSION[$this->flashname] = isset($_SESSION[$this->flashname]) ? $_SESSION[$this->flashname] : $this->flash;

        $this->closed = false;
        $this->started = true;
    }
    
    /**
     * Close session writing.
     */
    public function close()
    {
        if (!$this->started) {
            return true;
        }

        session_write_close();

        $this->closed = true;
        $this->started = false;
    }
    
    /**
     * Set an object in session.
     *
     * @param string $key
     * @param object $val
     */
    public function set($key, $val)
    {
        if (!$this->started) {
            $this->start();
        }

        $_SESSION[$key] = $val;
    }
    
    /**
     * Get an object in session.
     *
     * @param string $key
     *
     * @return object
     */
    public function get($key)
    {
        if (!$this->started) {
            $this->start();
        }
        
        $val = null;
        if (isset($_SESSION[$key])) {
            $val = $_SESSION[$key];
        }
        
        return $val;
    }
    
    /**
     * Destroy a session.
     */
    public function destroy()
    {
        $_SESSION = array();
    
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], 
                $params["domain"],
                $params["secure"], 
                $params["httponly"]
            );
        }
    
        session_destroy();
        
        $this->closed = false;
        $this->started = false;
    }
    
    /**
     * Get flash object from session.
     *
     * @return \Zanra\Framework\Flash
     */
    public function getFlash()
    {
        return $this->get($this->flashname);
    }
}
